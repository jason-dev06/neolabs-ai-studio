<?php

use App\Models\GeneratedImage;
use App\Models\User;

test('a user can delete their own image', function () {
    $user = User::factory()->create();

    $image = GeneratedImage::create([
        'user_id' => $user->id,
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => 'completed',
        'batch_id' => 'test-batch',
    ]);

    $this->actingAs($user)
        ->delete(route('image-generator.destroy', $image))
        ->assertRedirect();

    $this->assertDatabaseMissing('generated_images', ['id' => $image->id]);
});

test('a user cannot delete another users image', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $image = GeneratedImage::create([
        'user_id' => $owner->id,
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => 'completed',
        'batch_id' => 'test-batch',
    ]);

    $this->actingAs($other)
        ->delete(route('image-generator.destroy', $image))
        ->assertForbidden();

    $this->assertDatabaseHas('generated_images', ['id' => $image->id]);
});

test('guests cannot delete images', function () {
    $user = User::factory()->create();

    $image = GeneratedImage::create([
        'user_id' => $user->id,
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => 'completed',
        'batch_id' => 'test-batch',
    ]);

    $this->delete(route('image-generator.destroy', $image))
        ->assertRedirect(route('login'));
});
