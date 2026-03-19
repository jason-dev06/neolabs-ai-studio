<?php

namespace App\Services;

use App\DTOs\VideoGenerationData;
use App\Enums\GenerationStatus;
use App\Jobs\GenerateVideoJob;
use App\Models\GeneratedVideo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoGeneratorService
{
    public function generate(VideoGenerationData $data): GeneratedVideo
    {
        $batchId = Str::uuid()->toString();
        $creditCost = (int) ceil($data->qualityTier->baseCreditCost() * $data->duration->creditMultiplier());

        return DB::transaction(function () use ($data, $batchId, $creditCost) {
            $user = User::lockForUpdate()->find($data->userId);
            $user->deductCredits($creditCost);

            $video = GeneratedVideo::create([
                'user_id' => $data->userId,
                'prompt' => $data->prompt,
                'quality_tier' => $data->qualityTier,
                'duration' => $data->duration,
                'aspect_ratio' => $data->aspectRatio,
                'video_style' => $data->videoStyle,
                'credit_cost' => $creditCost,
                'status' => GenerationStatus::Pending,
                'batch_id' => $batchId,
            ]);

            GenerateVideoJob::dispatch($video);

            return $video;
        });
    }
}
