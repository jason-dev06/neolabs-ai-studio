<?php

namespace App\Services\ImageEditor;

use App\DTOs\ImageEditor\ApplyToolData;
use App\DTOs\ImageEditor\CreateSessionData;
use App\Enums\GenerationStatus;
use App\Enums\ImageEditSourceType;
use App\Jobs\ProcessImageEditJob;
use App\Models\GeneratedImage;
use App\Models\ImageEditSession;
use App\Models\ImageEditStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageEditorService
{
    public function createSession(CreateSessionData $data): ImageEditSession
    {
        if ($data->sourceType === ImageEditSourceType::Upload) {
            $fileName = Str::uuid()->toString().'.'.$data->uploadedFile->getClientOriginalExtension();
            $path = "editor-uploads/{$data->userId}/{$fileName}";
            $data->uploadedFile->storeAs("editor-uploads/{$data->userId}", $fileName, 'public');

            return ImageEditSession::create([
                'user_id' => $data->userId,
                'source_type' => $data->sourceType,
                'source_path' => $path,
                'source_url' => Storage::disk('public')->url($path),
            ]);
        }

        $generatedImage = GeneratedImage::findOrFail($data->generatedImageId);

        return ImageEditSession::create([
            'user_id' => $data->userId,
            'source_type' => $data->sourceType,
            'source_generated_image_id' => $generatedImage->id,
            'source_path' => $generatedImage->file_path,
            'source_url' => $generatedImage->file_url,
        ]);
    }

    public function applyTool(ApplyToolData $data): ImageEditStep
    {
        return DB::transaction(function () use ($data) {
            $user = User::lockForUpdate()->find($data->userId);
            $session = ImageEditSession::lockForUpdate()->findOrFail($data->sessionId);

            $tool = $data->tool;
            $creditCost = $tool->creditCost();

            $user->deductCredits($creditCost);

            // Delete any forward steps (branch overwrite on undo-then-edit)
            $session->steps()
                ->where('step_number', '>', $session->current_step)
                ->each(function (ImageEditStep $step) {
                    if ($step->file_path) {
                        Storage::disk('public')->delete($step->file_path);
                    }

                    $step->delete();
                });

            $nextStep = $session->current_step + 1;

            // Determine input image path
            $inputPath = $session->current_step === 0
                ? $session->source_path
                : $session->steps()->where('step_number', $session->current_step)->value('file_path');

            $step = ImageEditStep::create([
                'session_id' => $session->id,
                'step_number' => $nextStep,
                'tool' => $tool,
                'tool_settings' => $data->toolSettings ?: null,
                'credit_cost' => $creditCost,
                'status' => GenerationStatus::Pending,
            ]);

            ProcessImageEditJob::dispatch($step, $inputPath);

            return $step;
        });
    }

    public function undo(ImageEditSession $session): ImageEditSession
    {
        if ($session->current_step > 0) {
            $session->decrement('current_step');
            $session->refresh();
        }

        return $session;
    }

    public function redo(ImageEditSession $session): ImageEditSession
    {
        $maxStep = $session->maxCompletedStep();

        if ($session->current_step < $maxStep) {
            $session->increment('current_step');
            $session->refresh();
        }

        return $session;
    }

    public function deleteSession(ImageEditSession $session): void
    {
        // Clean up edited images directory
        Storage::disk('public')->deleteDirectory("edited-images/{$session->id}");

        // Clean up uploaded source if it was an upload
        if ($session->source_type === ImageEditSourceType::Upload) {
            Storage::disk('public')->delete($session->source_path);
        }

        $session->delete();
    }
}
