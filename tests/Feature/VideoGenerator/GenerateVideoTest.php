<?php

use App\Enums\GenerationStatus;
use App\Jobs\GenerateVideoJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('prompt is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $response->assertSessionHasErrors('prompt');
});

test('prompt max length is 1000', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => str_repeat('a', 1001),
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $response->assertSessionHasErrors('prompt');
});

test('quality tier must be a valid enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'invalid',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $response->assertSessionHasErrors('quality_tier');
});

test('duration must be a valid enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '10',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $response->assertSessionHasErrors('duration');
});

test('aspect ratio must be 16:9 or 9:16', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '1:1',
        'video_style' => 'cinematic',
    ]);

    $response->assertSessionHasErrors('aspect_ratio');
});

test('video style must be a valid enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'invalid',
    ]);

    $response->assertSessionHasErrors('video_style');
});

test('insufficient credits are rejected', function () {
    $user = User::factory()->create(['credits' => 5]);

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $response->assertSessionHasErrors('credits');
});

test('successful generation creates pending record and deducts credits', function () {
    $user = User::factory()->create(['credits' => 100]);

    $response = $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A cinematic sunset over the ocean',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->credits)->toBe(70);
    expect($user->generatedVideos)->toHaveCount(1);

    $video = $user->generatedVideos->first();
    expect($video->status)->toBe(GenerationStatus::Pending);
    expect($video->prompt)->toBe('A cinematic sunset over the ocean');
    expect($video->credit_cost)->toBe(30);

    Queue::assertPushed(GenerateVideoJob::class, 1);
});

test('credit cost scales with duration multiplier', function () {
    $user = User::factory()->create(['credits' => 200]);

    $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '8',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $user->refresh();
    // fast (30) × 2.0 (8s) = 60
    expect($user->credits)->toBe(140);
    expect($user->generatedVideos->first()->credit_cost)->toBe(60);
});

test('standard quality costs more than fast', function () {
    $user = User::factory()->create(['credits' => 200]);

    $this->actingAs($user)->post(route('video-generator.store'), [
        'prompt' => 'A test video',
        'quality_tier' => 'standard',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
    ]);

    $user->refresh();
    // standard (60) × 1.0 (4s) = 60
    expect($user->credits)->toBe(140);
    expect($user->generatedVideos->first()->credit_cost)->toBe(60);
});
