<?php

namespace App\DTOs;

use App\Enums\AspectRatio;
use App\Enums\VideoDuration;
use App\Enums\VideoQualityTier;
use App\Enums\VideoStyle;
use App\Http\Requests\VideoGenerator\GenerateVideoRequest;

readonly class VideoGenerationData
{
    public function __construct(
        public string $prompt,
        public VideoQualityTier $qualityTier,
        public VideoDuration $duration,
        public AspectRatio $aspectRatio,
        public VideoStyle $videoStyle,
        public int $userId,
    ) {}

    public static function fromRequest(GenerateVideoRequest $request): self
    {
        return new self(
            prompt: $request->validated('prompt'),
            qualityTier: VideoQualityTier::from($request->validated('quality_tier')),
            duration: VideoDuration::from($request->validated('duration')),
            aspectRatio: AspectRatio::from($request->validated('aspect_ratio')),
            videoStyle: VideoStyle::from($request->validated('video_style')),
            userId: $request->user()->id,
        );
    }
}
