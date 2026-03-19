<?php

namespace App\Http\Requests\VideoEditor;

use App\Enums\VideoEditorTool;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tool' => ['required', Rule::enum(VideoEditorTool::class)],
            'tool_settings' => ['sometimes', 'array'],
            'tool_settings.start_time' => ['required_if:tool,trim_cut', 'nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'tool_settings.end_time' => ['required_if:tool,trim_cut', 'nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'tool_settings.speed_factor' => ['required_if:tool,speed_control', 'nullable', 'string', 'in:0.25,0.5,1.5,2,4'],
            'tool_settings.language' => ['required_if:tool,auto_captions', 'nullable', 'string', 'in:en,es,fr,de,pt,ja,zh'],
            'tool_settings.effect' => ['required_if:tool,ai_effects', 'nullable', 'string', 'in:cinematic,vintage,glitch,neon,blur_bg,color_grade,slow_zoom,film_grain'],
            'tool_settings.extend_duration' => ['required_if:tool,extend_video', 'nullable', 'string', 'in:2,4,6'],
            'tool_settings.prompt' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $tool = VideoEditorTool::from($this->input('tool'));
            $creditCost = $tool->creditCost();

            if ($this->user()->credits < $creditCost) {
                $validator->errors()->add(
                    'credits',
                    "You need {$creditCost} credits but only have {$this->user()->credits}."
                );
            }
        });
    }
}
