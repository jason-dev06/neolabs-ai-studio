<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Enums\VideoEditorTool;
use App\Models\VideoEditStep;
use App\Services\VideoEditor\CaptionService;
use App\Services\VideoEditor\FFmpegService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessVideoEditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public int $backoff = 15;

    public function __construct(
        public VideoEditStep $step,
        public string $inputPath,
    ) {}

    public function handle(FFmpegService $ffmpeg, CaptionService $captions): void
    {
        $this->step->update(['status' => GenerationStatus::Processing]);

        $session = $this->step->session;
        $tool = $this->step->tool;

        $fileName = "{$this->step->step_number}.mp4";
        $directory = "edited-videos/{$session->id}";
        $filePath = "{$directory}/{$fileName}";

        $this->processWithFFmpeg($ffmpeg, $captions, $tool, $filePath);

        $this->step->update([
            'file_path' => $filePath,
            'file_url' => Storage::disk('public')->url($filePath),
            'status' => GenerationStatus::Completed,
        ]);

        $session->update(['current_step' => $this->step->step_number]);
    }

    /**
     * Process video locally using FFmpeg.
     *
     * For auto_captions, generates an SRT file first via CaptionService,
     * then burns the subtitles into the video with FFmpeg.
     */
    private function processWithFFmpeg(FFmpegService $ffmpeg, CaptionService $captions, VideoEditorTool $tool, string $outputFilePath): void
    {
        $absoluteInputPath = Storage::disk('public')->path($this->inputPath);

        $absoluteOutputPath = Storage::disk('public')->path($outputFilePath);
        $outputDir = dirname($absoluteOutputPath);

        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $settings = $this->step->tool_settings ?? [];

        // For auto captions, generate SRT first and inject path into settings
        $srtPath = null;

        if ($tool === VideoEditorTool::AutoCaptions) {
            $language = $settings['language'] ?? 'en';
            $srtContent = $captions->generateSrt($absoluteInputPath, $language);
            $srtPath = sys_get_temp_dir().'/'.uniqid('srt_').'.srt';
            file_put_contents($srtPath, $srtContent);
            $settings['srt_path'] = $srtPath;
        }

        try {
            $ffmpeg->process($tool, $absoluteInputPath, $absoluteOutputPath, $settings);
        } finally {
            if ($srtPath !== null) {
                @unlink($srtPath);
            }
        }

        if (! file_exists($absoluteOutputPath)) {
            throw new RuntimeException('FFmpeg did not produce an output file.');
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->step->update([
            'status' => GenerationStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);

        // Refund credits on failure
        $this->step->session->user->increment('credits', $this->step->credit_cost);

        Log::error('ProcessVideoEditJob failed', [
            'step_id' => $this->step->id,
            'session_id' => $this->step->session_id,
            'error' => $exception->getMessage(),
        ]);
    }
}
