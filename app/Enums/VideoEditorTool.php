<?php

namespace App\Enums;

enum VideoEditorTool: string
{
    case TrimCut = 'trim_cut';
    case SpeedControl = 'speed_control';
    case AutoCaptions = 'auto_captions';
    case AiEffects = 'ai_effects';
    case ExtendVideo = 'extend_video';

    public function creditCost(): int
    {
        return match ($this) {
            self::TrimCut => 5,
            self::SpeedControl => 5,
            self::AutoCaptions => 10,
            self::AiEffects => 15,
            self::ExtendVideo => 20,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::TrimCut => 'Trim & Cut',
            self::SpeedControl => 'Speed Control',
            self::AutoCaptions => 'Auto Captions',
            self::AiEffects => 'AI Effects',
            self::ExtendVideo => 'Extend Video',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::TrimCut => 'Cut and trim video segments',
            self::SpeedControl => 'Slow motion or speed up',
            self::AutoCaptions => 'Generate and add captions with AI',
            self::AiEffects => 'Add visual effects and filters',
            self::ExtendVideo => 'AI-extend your video with more content',
        };
    }

    /**
     * Whether this tool uses local FFmpeg processing rather than AI generation.
     */
    public function isLocalProcessing(): bool
    {
        return true;
    }

    /**
     * Human-readable description of the FFmpeg operation for logging.
     */
    public function ffmpegDescription(array $settings = []): string
    {
        return match ($this) {
            self::TrimCut => sprintf('Trim video from %s to %s', $settings['start_time'] ?? '00:00', $settings['end_time'] ?? '00:10'),
            self::SpeedControl => sprintf('Change speed to %sx', $settings['speed_factor'] ?? '1.5'),
            self::AutoCaptions => sprintf('Burn captions in %s', $settings['language'] ?? 'en'),
            self::AiEffects => sprintf('Apply %s effect', $settings['effect'] ?? 'cinematic'),
            self::ExtendVideo => sprintf('Extend video by %s seconds with freeze-frame', $settings['extend_duration'] ?? '4'),
        };
    }
}
