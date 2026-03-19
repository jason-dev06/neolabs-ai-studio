<?php

namespace App\Jobs;

use App\Enums\GenerationStatus;
use App\Models\ImageEditStep;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\LocalImage;
use Laravel\Ai\Image;

class ProcessImageEditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public int $backoff = 10;

    public function __construct(
        public ImageEditStep $step,
        public string $inputPath,
    ) {}

    public function handle(): void
    {
        $this->step->update(['status' => GenerationStatus::Processing]);

        $session = $this->step->session;
        $tool = $this->step->tool;
        $prompt = $tool->prompt($this->step->tool_settings ?? []);

        $sourceImagePath = Storage::disk('public')->path($this->inputPath);

        $result = Image::of($prompt)
            ->attachments([new LocalImage($sourceImagePath)])
            ->timeout(120)
            ->generate('openai');

        $fileName = "{$this->step->step_number}.png";
        $directory = "edited-images/{$session->id}";
        $filePath = "{$directory}/{$fileName}";

        $result->storeAs($directory, $fileName, 'public');

        $this->step->update([
            'file_path' => $filePath,
            'file_url' => Storage::disk('public')->url($filePath),
            'status' => GenerationStatus::Completed,
        ]);

        $session->update(['current_step' => $this->step->step_number]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->step->update([
            'status' => GenerationStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);

        // Refund credits on failure
        $this->step->session->user->increment('credits', $this->step->credit_cost);
    }
}
