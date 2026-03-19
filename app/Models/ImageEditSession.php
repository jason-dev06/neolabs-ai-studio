<?php

namespace App\Models;

use App\Enums\GenerationStatus;
use App\Enums\ImageEditSourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImageEditSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'source_type',
        'source_generated_image_id',
        'source_path',
        'source_url',
        'current_step',
        'disk',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => ImageEditSourceType::class,
            'current_step' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceGeneratedImage(): BelongsTo
    {
        return $this->belongsTo(GeneratedImage::class, 'source_generated_image_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ImageEditStep::class, 'session_id')->orderBy('step_number');
    }

    /**
     * Get the current display image URL (source if step 0, otherwise the step's output).
     */
    public function currentImageUrl(): string
    {
        if ($this->current_step === 0) {
            return $this->source_url;
        }

        $step = $this->steps->firstWhere('step_number', $this->current_step);

        return $step?->file_url ?? $this->source_url;
    }

    /**
     * Check if there is an active (pending/processing) step.
     */
    public function hasActiveStep(): bool
    {
        return $this->steps()
            ->whereIn('status', [
                GenerationStatus::Pending->value,
                GenerationStatus::Processing->value,
            ])
            ->exists();
    }

    /**
     * Get the highest completed step number.
     */
    public function maxCompletedStep(): int
    {
        return (int) $this->steps()
            ->where('status', GenerationStatus::Completed->value)
            ->max('step_number');
    }
}
