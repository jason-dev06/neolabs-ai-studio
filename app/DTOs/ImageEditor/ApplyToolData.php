<?php

namespace App\DTOs\ImageEditor;

use App\Enums\ImageEditorTool;
use App\Http\Requests\ImageEditor\ApplyToolRequest;

readonly class ApplyToolData
{
    public function __construct(
        public int $sessionId,
        public ImageEditorTool $tool,
        public array $toolSettings,
        public int $userId,
    ) {}

    public static function fromRequest(ApplyToolRequest $request, int $sessionId): self
    {
        return new self(
            sessionId: $sessionId,
            tool: ImageEditorTool::from($request->validated('tool')),
            toolSettings: $request->validated('tool_settings', []),
            userId: $request->user()->id,
        );
    }
}
