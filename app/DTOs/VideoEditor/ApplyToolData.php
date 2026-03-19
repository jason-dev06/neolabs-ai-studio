<?php

namespace App\DTOs\VideoEditor;

use App\Enums\VideoEditorTool;
use App\Http\Requests\VideoEditor\ApplyToolRequest;

readonly class ApplyToolData
{
    public function __construct(
        public int $sessionId,
        public VideoEditorTool $tool,
        public array $toolSettings,
        public int $userId,
    ) {}

    public static function fromRequest(ApplyToolRequest $request, int $sessionId): self
    {
        return new self(
            sessionId: $sessionId,
            tool: VideoEditorTool::from($request->validated('tool')),
            toolSettings: $request->validated('tool_settings', []),
            userId: $request->user()->id,
        );
    }
}
