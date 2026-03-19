<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Models\GeneratedImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;

class GenerateImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public int $backoff = 10;

    public function __construct(
        public GeneratedImage $generatedImage,
    ) {}

    public function handle(): void
    {
        $this->generatedImage->update(['status' => GenerationStatus::Processing]);

        $orientation = $this->generatedImage->aspect_ratio->sdkOrientation();
        $quality = $this->generatedImage->quality_tier->sdkQuality();

        $result = Image::of($this->generatedImage->prompt)
            ->quality($quality)
            ->{$orientation}()
            ->timeout(120)
            ->generate('openai');

        $fileName = "{$this->generatedImage->id}.png";
        $filePath = "generated-images/{$fileName}";
        $result->storeAs('generated-images', $fileName, 'public');

        $this->generatedImage->update([
            'file_path' => $filePath,
            'file_url' => Storage::disk('public')->url($filePath),
            'status' => GenerationStatus::Completed,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->generatedImage->update([
            'status' => GenerationStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
    }
}
