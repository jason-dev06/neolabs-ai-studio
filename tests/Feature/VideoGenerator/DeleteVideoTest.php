<?php

use App\Models\GeneratedVideo;
use App\Models\User;

test('a user can delete their own video', function () {
    $user = User::factory()->create();

    $video = GeneratedVideo::create([
        'user_id' => $user->id,
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 30,
        'status' => 'completed',
        'batch_id' => 'test-batch',
    ]);

    $this->actingAs($user)
        ->delete(route('video-generator.destroy', $video))
        ->assertRedirect();

    $this->assertDatabaseMissing('generated_videos', ['id' => $video->id]);
});

test('a user cannot delete another users video', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $video = GeneratedVideo::create([
        'user_id' => $owner->id,
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 30,
        'status' => 'completed',
        'batch_id' => 'test-batch',
    ]);

    $this->actingAs($other)
        ->delete(route('video-generator.destroy', $video))
        ->assertForbidden();

    $this->assertDatabaseHas('generated_videos', ['id' => $video->id]);
});

test('guests cannot delete videos', function () {
    $user = User::factory()->create();

    $video = GeneratedVideo::create([
        'user_id' => $user->id,
        'prompt' => 'A test video',
        'quality_tier' => 'fast',
        'duration' => '4',
        'aspect_ratio' => '16:9',
        'video_style' => 'cinematic',
        'credit_cost' => 30,
        'status' => 'completed',
        'batch_id' => 'test-batch',
    ]);

    $this->delete(route('video-generator.destroy', $video))
        ->assertRedirect(route('login'));
});
