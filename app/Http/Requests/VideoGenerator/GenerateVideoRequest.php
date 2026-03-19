<?php

namespace App\Http\Requests\VideoGenerator;

use App\Enums\VideoDuration;
use App\Enums\VideoQualityTier;
use App\Enums\VideoStyle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:1000'],
            'quality_tier' => ['required', Rule::enum(VideoQualityTier::class)],
            'duration' => ['required', Rule::enum(VideoDuration::class)],
            'aspect_ratio' => ['required', Rule::in(['16:9', '9:16'])],
            'video_style' => ['required', Rule::enum(VideoStyle::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $tier = VideoQualityTier::from($this->input('quality_tier'));
            $duration = VideoDuration::from($this->input('duration'));
            $totalCost = (int) ceil($tier->baseCreditCost() * $duration->creditMultiplier());

            if ($this->user()->credits < $totalCost) {
                $validator->errors()->add(
                    'credits',
                    "You need {$totalCost} credits but only have {$this->user()->credits}."
                );
            }
        });
    }
}
