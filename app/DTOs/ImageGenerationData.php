<?php

namespace App\DTOs;

use App\Enums\AspectRatio;
use App\Enums\QualityTier;
use App\Http\Requests\ImageGenerator\GenerateImageRequest;

readonly class ImageGenerationData
{
    public function __construct(
        public string $prompt,
        public QualityTier $qualityTier,
        public AspectRatio $aspectRatio,
        public int $numberOfImages,
        public int $userId,
    ) {}

    public static function fromRequest(GenerateImageRequest $request): self
    {
        return new self(
            prompt: $request->validated('prompt'),
            qualityTier: QualityTier::from($request->validated('quality_tier')),
            aspectRatio: AspectRatio::from($request->validated('aspect_ratio')),
            numberOfImages: (int) $request->validated('number_of_images'),
            userId: $request->user()->id,
        );
    }
}
