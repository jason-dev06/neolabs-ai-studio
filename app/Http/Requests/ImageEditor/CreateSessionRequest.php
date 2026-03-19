<?php

namespace App\Http\Requests\ImageEditor;

use App\Enums\ImageEditSourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_type' => ['required', Rule::enum(ImageEditSourceType::class)],
            'generated_image_id' => [
                'required_if:source_type,generated',
                'nullable',
                'integer',
                'exists:generated_images,id',
            ],
            'image' => [
                'required_if:source_type,upload',
                'nullable',
                'image',
                'max:10240',
                'mimes:jpg,jpeg,png,webp',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->input('source_type') === 'generated') {
                $imageExists = $this->user()
                    ->generatedImages()
                    ->where('id', $this->input('generated_image_id'))
                    ->where('status', 'completed')
                    ->exists();

                if (! $imageExists) {
                    $validator->errors()->add(
                        'generated_image_id',
                        'The selected image does not belong to you or is not ready.'
                    );
                }
            }
        });
    }
}
