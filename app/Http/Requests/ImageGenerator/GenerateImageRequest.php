<?php

namespace App\Http\Requests\ImageGenerator;

use App\Enums\AspectRatio;
use App\Enums\QualityTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:1000'],
            'quality_tier' => ['required', Rule::enum(QualityTier::class)],
            'aspect_ratio' => ['required', Rule::enum(AspectRatio::class)],
            'number_of_images' => ['required', 'integer', 'in:1,2,3,4'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $tier = QualityTier::from($this->input('quality_tier'));
            $totalCost = $tier->creditCost() * (int) $this->input('number_of_images');

            if ($this->user()->credits < $totalCost) {
                $validator->errors()->add(
                    'credits',
                    "You need {$totalCost} credits but only have {$this->user()->credits}."
                );
            }
        });
    }
}
