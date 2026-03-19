<?php

namespace App\DTOs\VideoEditor;

use App\Enums\VideoEditSourceType;
use App\Http\Requests\VideoEditor\CreateSessionRequest;
use Illuminate\Http\UploadedFile;

readonly class CreateSessionData
{
    public function __construct(
        public VideoEditSourceType $sourceType,
        public ?int $generatedVideoId,
        public ?UploadedFile $uploadedFile,
        public int $userId,
    ) {}

    public static function fromRequest(CreateSessionRequest $request): self
    {
        return new self(
            sourceType: VideoEditSourceType::from($request->validated('source_type')),
            generatedVideoId: $request->validated('generated_video_id'),
            uploadedFile: $request->file('video'),
            userId: $request->user()->id,
        );
    }
}
