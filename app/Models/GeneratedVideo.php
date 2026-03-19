<?php

namespace App\Models;

use App\Enums\AspectRatio;
use App\Enums\GenerationStatus;
use App\Enums\VideoDuration;
use App\Enums\VideoQualityTier;
use App\Enums\VideoStyle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedVideo extends Model
{
    protected $fillable = [
        'user_id',
        'prompt',
        'quality_tier',
        'duration',
        'aspect_ratio',
        'video_style',
        'credit_cost',
        'status',
        'disk',
        'file_path',
        'file_url',
        'thumbnail_url',
        'error_message',
        'batch_id',
    ];

    protected function casts(): array
    {
        return [
            'quality_tier' => VideoQualityTier::class,
            'duration' => VideoDuration::class,
            'aspect_ratio' => AspectRatio::class,
            'video_style' => VideoStyle::class,
            'status' => GenerationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
