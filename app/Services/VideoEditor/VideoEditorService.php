<?php

namespace App\Services\VideoEditor;

use App\DTOs\VideoEditor\ApplyToolData;
use App\DTOs\VideoEditor\CreateSessionData;
use App\Enums\GenerationStatus;
use App\Enums\VideoEditSourceType;
use App\Jobs\ProcessVideoEditJob;
use App\Models\GeneratedVideo;
use App\Models\User;
use App\Models\VideoEditSession;
use App\Models\VideoEditStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoEditorService
{
    public function createSession(CreateSessionData $data): VideoEditSession
    {
        if ($data->sourceType === VideoEditSourceType::Upload) {
            $fileName = Str::uuid()->toString().'.'.$data->uploadedFile->getClientOriginalExtension();
            $path = "video-editor-uploads/{$data->userId}/{$fileName}";
            $data->uploadedFile->storeAs("video-editor-uploads/{$data->userId}", $fileName, 'public');

            return VideoEditSession::create([
                'user_id' => $data->userId,
                'source_type' => $data->sourceType,
                'source_path' => $path,
                'source_url' => Storage::disk('public')->url($path),
            ]);
        }

        $generatedVideo = GeneratedVideo::findOrFail($data->generatedVideoId);

        return VideoEditSession::create([
            'user_id' => $data->userId,
            'source_type' => $data->sourceType,
            'source_generated_video_id' => $generatedVideo->id,
            'source_path' => $generatedVideo->file_path,
            'source_url' => $generatedVideo->file_url,
        ]);
    }

    public function applyTool(ApplyToolData $data): VideoEditStep
    {
        return DB::transaction(function () use ($data) {
            $user = User::lockForUpdate()->find($data->userId);
            $session = VideoEditSession::lockForUpdate()->findOrFail($data->sessionId);

            $tool = $data->tool;
            $creditCost = $tool->creditCost();

            $user->deductCredits($creditCost);

            // Delete any forward steps (branch overwrite on undo-then-edit)
            $session->steps()
                ->where('step_number', '>', $session->current_step)
                ->each(function (VideoEditStep $step) {
                    if ($step->file_path) {
                        Storage::disk('public')->delete($step->file_path);
                    }

                    $step->delete();
                });

            $nextStep = $session->current_step + 1;

            // Determine input video path
            $inputPath = $session->current_step === 0
                ? $session->source_path
                : $session->steps()->where('step_number', $session->current_step)->value('file_path');

            $step = VideoEditStep::create([
                'session_id' => $session->id,
                'step_number' => $nextStep,
                'tool' => $tool,
                'tool_settings' => $data->toolSettings ?: null,
                'credit_cost' => $creditCost,
                'status' => GenerationStatus::Pending,
            ]);

            ProcessVideoEditJob::dispatch($step, $inputPath);

            return $step;
        });
    }

    public function undo(VideoEditSession $session): VideoEditSession
    {
        if ($session->current_step > 0) {
            $session->decrement('current_step');
            $session->refresh();
        }

        return $session;
    }

    public function redo(VideoEditSession $session): VideoEditSession
    {
        $maxStep = $session->maxCompletedStep();

        if ($session->current_step < $maxStep) {
            $session->increment('current_step');
            $session->refresh();
        }

        return $session;
    }

    public function deleteSession(VideoEditSession $session): void
    {
        // Clean up edited videos directory
        Storage::disk('public')->deleteDirectory("edited-videos/{$session->id}");

        // Clean up uploaded source if it was an upload
        if ($session->source_type === VideoEditSourceType::Upload) {
            Storage::disk('public')->delete($session->source_path);
        }

        $session->delete();
    }
}
