<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Models\GeneratedVideo;
use App\Services\GeminiVideoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public int $backoff = 30;

    public function __construct(
        public GeneratedVideo $generatedVideo,
    ) {}

    public function handle(GeminiVideoClient $client): void
    {
        $this->generatedVideo->update(['status' => GenerationStatus::Processing]);

        $model = $this->generatedVideo->quality_tier->model();
        $prompt = $this->generatedVideo->prompt;
        $duration = $this->generatedVideo->duration->seconds();
        $aspectRatio = $this->generatedVideo->aspect_ratio->value;

        $operationName = $client->generate($model, $prompt, $duration, $aspectRatio);

        $result = $client->pollUntilDone($operationName);

        $videoUri = $result['response']['generateVideoResponse']['generatedSamples'][0]['video']['uri']
            ?? throw new \RuntimeException('No video URI in Gemini response.');

        $videoContent = $client->downloadVideo($videoUri);

        $filePath = "generated-videos/{$this->generatedVideo->id}.mp4";
        Storage::disk('public')->put($filePath, $videoContent);

        $this->generatedVideo->update([
            'file_path' => $filePath,
            'file_url' => Storage::disk('public')->url($filePath),
            'status' => GenerationStatus::Completed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Video generation failed.', [
            'video_id' => $this->generatedVideo->id,
            'prompt' => $this->generatedVideo->prompt,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $this->generatedVideo->update([
            'status' => GenerationStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
