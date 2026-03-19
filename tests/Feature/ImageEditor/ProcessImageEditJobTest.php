<?php

use App\Enums\GenerationStatus;
use App\Jobs\ProcessImageEditJob;
use App\Models\ImageEditSession;
use App\Models\ImageEditStep;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;

test('job updates status to completed and stores file', function () {
    Storage::fake('public');
    Image::fake();

    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    // Create a fake source file
    Storage::disk('public')->put($session->source_path, 'fake-image-content');

    $step = ImageEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'enhance',
        'credit_cost' => 5,
        'status' => GenerationStatus::Pending,
    ]);

    (new ProcessImageEditJob($step, $session->source_path))->handle();

    $step->refresh();
    $session->refresh();

    expect($step->status)->toBe(GenerationStatus::Completed);
    expect($step->file_path)->toBe("edited-images/{$session->id}/1.png");
    expect($session->current_step)->toBe(1);

    Image::assertGenerated(fn ($prompt) => str_contains($prompt->prompt, 'Enhance'));
});

test('job sets failed status and refunds credits on exception', function () {
    $user = User::factory()->create(['credits' => 95]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $step = ImageEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'tool' => 'enhance',
        'credit_cost' => 5,
        'status' => GenerationStatus::Processing,
    ]);

    $job = new ProcessImageEditJob($step, $session->source_path);
    $job->failed(new RuntimeException('AI provider error'));

    $step->refresh();
    $user->refresh();

    expect($step->status)->toBe(GenerationStatus::Failed);
    expect($step->error_message)->toBe('AI provider error');
    expect($user->credits)->toBe(100); // refunded 5 credits
});
