<?php

namespace App\Services;

use App\DTOs\ImageGenerationData;
use App\Enums\GenerationStatus;
use App\Jobs\GenerateImageJob;
use App\Models\GeneratedImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImageGeneratorService
{
    public function generate(ImageGenerationData $data): Collection
    {
        $batchId = Str::uuid()->toString();
        $creditCost = $data->qualityTier->creditCost();
        $totalCost = $creditCost * $data->numberOfImages;

        return DB::transaction(function () use ($data, $batchId, $creditCost, $totalCost) {
            $user = User::lockForUpdate()->find($data->userId);
            $user->deductCredits($totalCost);

            $images = new Collection;

            for ($i = 0; $i < $data->numberOfImages; $i++) {
                $image = GeneratedImage::create([
                    'user_id' => $data->userId,
                    'prompt' => $data->prompt,
                    'quality_tier' => $data->qualityTier,
                    'aspect_ratio' => $data->aspectRatio,
                    'credit_cost' => $creditCost,
                    'status' => GenerationStatus::Pending,
                    'batch_id' => $batchId,
                ]);

                $images->push($image);
                GenerateImageJob::dispatch($image);
            }

            return $images;
        });
    }
}
