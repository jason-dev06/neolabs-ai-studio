<?php

namespace App\Services\VideoEditor;

use App\Enums\VideoEditorTool;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class FFmpegService
{
    /**
     * Process a video using FFmpeg based on the tool and its settings.
     *
     * @throws RuntimeException
     */
    public function process(VideoEditorTool $tool, string $inputPath, string $outputPath, array $settings = []): void
    {
        $command = match ($tool) {
            VideoEditorTool::TrimCut => $this->buildTrimCommand($inputPath, $outputPath, $settings),
            VideoEditorTool::SpeedControl => $this->buildSpeedCommand($inputPath, $outputPath, $settings),
            VideoEditorTool::AutoCaptions => $this->buildCaptionsCommand($inputPath, $outputPath, $settings),
            VideoEditorTool::AiEffects => $this->buildEffectsCommand($inputPath, $outputPath, $settings),
            VideoEditorTool::ExtendVideo => $this->buildExtendCommand($inputPath, $outputPath, $settings),
        };

        $result = Process::timeout(300)->run($command);

        if ($result->failed()) {
            throw new RuntimeException("FFmpeg processing failed: {$result->errorOutput()}");
        }
    }

    /**
     * @return string[]
     */
    private function buildTrimCommand(string $inputPath, string $outputPath, array $settings): array
    {
        $startTime = $settings['start_time'] ?? '00:00';
        $endTime = $settings['end_time'] ?? '00:10';

        // Convert mm:ss to seconds-based format for FFmpeg
        $startSeconds = $this->timeToSeconds($startTime);
        $endSeconds = $this->timeToSeconds($endTime);

        if ($endSeconds <= $startSeconds) {
            throw new RuntimeException('End time must be after start time.');
        }

        return [
            'ffmpeg', '-y',
            '-ss', (string) $startSeconds,
            '-to', (string) $endSeconds,
            '-i', $inputPath,
            '-c', 'copy',
            '-avoid_negative_ts', 'make_zero',
            $outputPath,
        ];
    }

    /**
     * @return string[]
     */
    private function buildSpeedCommand(string $inputPath, string $outputPath, array $settings): array
    {
        $speedFactor = (float) ($settings['speed_factor'] ?? 1.5);

        // Video: setpts divides by speed (faster = lower PTS value)
        $videoPts = sprintf('%.4f', 1 / $speedFactor);

        // Audio: atempo only supports 0.5-100.0 range, chain if needed
        $atempoFilters = $this->buildAtempoFilter($speedFactor);

        return [
            'ffmpeg', '-y',
            '-i', $inputPath,
            '-filter:v', "setpts={$videoPts}*PTS",
            '-filter:a', $atempoFilters,
            '-preset', 'fast',
            $outputPath,
        ];
    }

    /**
     * Burn SRT subtitles onto the video.
     *
     * @return string[]
     */
    private function buildCaptionsCommand(string $inputPath, string $outputPath, array $settings): array
    {
        $srtPath = $settings['srt_path'] ?? throw new RuntimeException('SRT file path is required for auto captions.');

        // FFmpeg subtitles filter requires escaping special chars in the path.
        // Since we pass args as an array (no shell), we must NOT add shell quotes.
        // Instead, escape chars that are special to FFmpeg's filter parser.
        $escapedSrtPath = str_replace(
            ['\\', ':', "'", '[', ']'],
            ['\\\\', '\\:', "\\'", '\\[', '\\]'],
            $srtPath,
        );

        return [
            'ffmpeg', '-y',
            '-i', $inputPath,
            '-vf', "subtitles=filename={$escapedSrtPath}",
            '-c:a', 'copy',
            '-preset', 'fast',
            $outputPath,
        ];
    }

    /**
     * Apply a visual effect using FFmpeg filters.
     *
     * @return string[]
     */
    private function buildEffectsCommand(string $inputPath, string $outputPath, array $settings): array
    {
        $effect = $settings['effect'] ?? 'cinematic';

        $filter = $this->effectToFilter($effect);

        return [
            'ffmpeg', '-y',
            '-i', $inputPath,
            '-vf', $filter,
            '-c:a', 'copy',
            '-preset', 'fast',
            $outputPath,
        ];
    }

    /**
     * Map effect name to FFmpeg filter string.
     */
    public function effectToFilter(string $effect): string
    {
        return match ($effect) {
            'cinematic' => 'crop=iw:iw/2.39,pad=iw:ih+120:0:60:black,eq=contrast=1.1:saturation=0.85',
            'vintage' => 'curves=vintage,eq=saturation=0.6',
            'glitch' => 'rgbashift=rh=-3:bv=3,noise=alls=40:allf=t',
            'neon' => 'eq=saturation=2.0:contrast=1.3,edgedetect=low=0.1:high=0.3',
            'blur_bg' => 'boxblur=10:5',
            'color_grade' => 'eq=contrast=1.2:brightness=0.05:saturation=1.1',
            'slow_zoom' => "zoompan=z='min(zoom+0.001,1.3)':d=1:s=1280x720:fps=30",
            'film_grain' => 'noise=alls=25:allf=t+u',
            default => throw new RuntimeException("Unknown effect: {$effect}"),
        };
    }

    /**
     * Extend the video by freezing the last frame using tpad.
     *
     * @return string[]
     */
    private function buildExtendCommand(string $inputPath, string $outputPath, array $settings): array
    {
        $duration = (int) ($settings['extend_duration'] ?? 4);

        return [
            'ffmpeg', '-y',
            '-i', $inputPath,
            '-vf', "tpad=stop_mode=clone:stop_duration={$duration}",
            '-c:a', 'copy',
            '-preset', 'fast',
            $outputPath,
        ];
    }

    /**
     * Build atempo filter chain. FFmpeg atempo supports 0.5-100.0 range per filter,
     * so we chain multiple filters for values outside that range.
     */
    private function buildAtempoFilter(float $speed): string
    {
        $filters = [];

        // For slow-down below 0.5x, chain multiple atempo filters
        while ($speed < 0.5) {
            $filters[] = 'atempo=0.5';
            $speed /= 0.5;
        }

        // For speed-up above 100x (unlikely but safe), chain
        while ($speed > 100.0) {
            $filters[] = 'atempo=100.0';
            $speed /= 100.0;
        }

        $filters[] = sprintf('atempo=%.4f', $speed);

        return implode(',', $filters);
    }

    private function timeToSeconds(string $time): int
    {
        $parts = explode(':', $time);

        if (count($parts) !== 2) {
            throw new RuntimeException("Invalid time format: {$time}. Expected mm:ss.");
        }

        return ((int) $parts[0] * 60) + (int) $parts[1];
    }
}
