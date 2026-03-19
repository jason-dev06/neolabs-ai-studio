<?php

use App\Enums\GenerationStatus;
use App\Enums\VideoEditSourceType;
use App\Models\GeneratedVideo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can create a session by uploading a video', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-editor.store'), [
        'source_type' => 'upload',
        'video' => UploadedFile::fake()->create('clip.mp4', 5000, 'video/mp4'),
    ]);

    $response->assertRedirect();

    $session = $user->videoEditSessions()->first();
    expect($session)->not->toBeNull();
    expect($session->source_type)->toBe(VideoEditSourceType::Upload);
    expect($session->current_step)->toBe(0);
    Storage::disk('public')->assertExists($session->source_path);
});

test('a user can create a session from a generated video', function () {
    $user = User::factory()->create();

    $generatedVideo = GeneratedVideo::create([
        'user_id' => $user->id,
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 10,
        'status' => GenerationStatus::Completed->value,
        'file_path' => 'generated-videos/1.mp4',
        'file_url' => '/storage/generated-videos/1.mp4',
        'batch_id' => fake()->uuid(),
    ]);

    $response = $this->actingAs($user)->post(route('video-editor.store'), [
        'source_type' => 'generated',
        'generated_video_id' => $generatedVideo->id,
    ]);

    $response->assertRedirect();

    $session = $user->videoEditSessions()->first();
    expect($session)->not->toBeNull();
    expect($session->source_type)->toBe(VideoEditSourceType::Generated);
    expect($session->source_generated_video_id)->toBe($generatedVideo->id);
});

test('source type is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('video-editor.store'), [])
        ->assertSessionHasErrors('source_type');
});

test('uploaded video must be a valid video file', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('video-editor.store'), [
        'source_type' => 'upload',
        'video' => UploadedFile::fake()->create('document.pdf', 100),
    ])->assertSessionHasErrors('video');
});

test('uploaded video must not exceed 100MB', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('video-editor.store'), [
        'source_type' => 'upload',
        'video' => UploadedFile::fake()->create('large.mp4', 105000, 'video/mp4'),
    ])->assertSessionHasErrors('video');
});

test('user cannot select another users generated video', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $generatedVideo = GeneratedVideo::create([
        'user_id' => $owner->id,
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 10,
        'status' => GenerationStatus::Completed->value,
        'file_path' => 'generated-videos/1.mp4',
        'file_url' => '/storage/generated-videos/1.mp4',
        'batch_id' => fake()->uuid(),
    ]);

    $this->actingAs($other)->post(route('video-editor.store'), [
        'source_type' => 'generated',
        'generated_video_id' => $generatedVideo->id,
    ])->assertSessionHasErrors('generated_video_id');
});
