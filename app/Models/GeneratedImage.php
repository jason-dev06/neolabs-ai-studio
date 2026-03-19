<?php

namespace App\Models;

use App\Enums\AspectRatio;
use App\Enums\GenerationStatus;
use App\Enums\QualityTier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedImage extends Model
{
    protected $fillable = [
        'user_id',
        'prompt',
        'quality_tier',
        'aspect_ratio',
        'credit_cost',
        'status',
        'disk',
        'file_path',
        'file_url',
        'error_message',
        'batch_id',
    ];

    protected function casts(): array
    {
        return [
            'quality_tier' => QualityTier::class,
            'aspect_ratio' => AspectRatio::class,
            'status' => GenerationStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
