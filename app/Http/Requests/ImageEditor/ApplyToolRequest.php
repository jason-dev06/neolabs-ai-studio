<?php

namespace App\Http\Requests\ImageEditor;

use App\Enums\ImageEditorTool;
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
            'tool' => ['required', Rule::enum(ImageEditorTool::class)],
            'tool_settings' => ['sometimes', 'array'],
            'tool_settings.prompt' => ['required_if:tool,inpaint', 'nullable', 'string', 'max:500'],
            'tool_settings.erase_prompt' => ['required_if:tool,erase_object', 'nullable', 'string', 'max:200'],
            'tool_settings.style' => ['required_if:tool,style_transfer', 'nullable', 'string', 'in:painterly,watercolor,sketch,anime,oil_painting,pixel_art,photorealistic,pop-art,impressionist,cubist'],
            'tool_settings.direction' => ['required_if:tool,extend', 'nullable', 'string', 'in:up,down,left,right,all'],
            'tool_settings.scale_factor' => ['sometimes', 'nullable', 'integer', 'in:2,4'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $tool = ImageEditorTool::from($this->input('tool'));
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
