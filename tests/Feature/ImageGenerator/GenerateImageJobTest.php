<?php

use App\Enums\GenerationStatus;
use App\Jobs\GenerateImageJob;
use App\Models\GeneratedImage;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Image;

test('job updates status to completed and stores file', function () {
    Storage::fake('public');
    Image::fake();

    $user = User::factory()->create();
    $image = GeneratedImage::create([
        'user_id' => $user->id,
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => 'pending',
        'batch_id' => fake()->uuid(),
    ]);

    (new GenerateImageJob($image))->handle();

    $image->refresh();
    expect($image->status)->toBe(GenerationStatus::Completed);
    expect($image->file_path)->toBe("generated-images/{$image->id}.png");

    Image::assertGenerated(fn ($prompt) => $prompt->prompt === 'A test image');
});

test('job sets failed status on exception', function () {
    $user = User::factory()->create();
    $image = GeneratedImage::create([
        'user_id' => $user->id,
        'prompt' => 'A failing image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => 'pending',
        'batch_id' => fake()->uuid(),
    ]);

    $job = new GenerateImageJob($image);
    $job->failed(new RuntimeException('API rate limit exceeded'));

    $image->refresh();
    expect($image->status)->toBe(GenerationStatus::Failed);
    expect($image->error_message)->toBe('API rate limit exceeded');
});
