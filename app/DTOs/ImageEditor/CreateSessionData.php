<?php

namespace App\DTOs\ImageEditor;

use App\Enums\ImageEditSourceType;
use App\Http\Requests\ImageEditor\CreateSessionRequest;
use Illuminate\Http\UploadedFile;

readonly class CreateSessionData
{
    public function __construct(
        public ImageEditSourceType $sourceType,
        public ?int $generatedImageId,
        public ?UploadedFile $uploadedFile,
        public int $userId,
    ) {}

    public static function fromRequest(CreateSessionRequest $request): self
    {
        return new self(
            sourceType: ImageEditSourceType::from($request->validated('source_type')),
            generatedImageId: $request->validated('generated_image_id'),
            uploadedFile: $request->file('image'),
            userId: $request->user()->id,
        );
    }
}
