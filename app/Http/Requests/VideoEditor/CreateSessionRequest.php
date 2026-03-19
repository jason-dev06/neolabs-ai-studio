<?php

namespace App\Http\Requests\VideoEditor;

use App\Enums\VideoEditSourceType;
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
            'source_type' => ['required', Rule::enum(VideoEditSourceType::class)],
            'generated_video_id' => [
                'required_if:source_type,generated',
                'nullable',
                'integer',
                'exists:generated_videos,id',
            ],
            'video' => [
                'required_if:source_type,upload',
                'nullable',
                'file',
                'max:102400',
                'mimes:mp4,mov,webm',
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
                $videoExists = $this->user()
                    ->generatedVideos()
                    ->where('id', $this->input('generated_video_id'))
                    ->where('status', 'completed')
                    ->exists();

                if (! $videoExists) {
                    $validator->errors()->add(
                        'generated_video_id',
                        'The selected video does not belong to you or is not ready.'
                    );
                }
            }
        });
    }
}
