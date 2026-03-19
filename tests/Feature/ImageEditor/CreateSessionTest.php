<?php

use App\Enums\GenerationStatus;
use App\Enums\ImageEditSourceType;
use App\Models\GeneratedImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a user can create a session by uploading an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('image-editor.store'), [
        'source_type' => 'upload',
        'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
    ]);

    $response->assertRedirect();

    $session = $user->imageEditSessions()->first();
    expect($session)->not->toBeNull();
    expect($session->source_type)->toBe(ImageEditSourceType::Upload);
    expect($session->current_step)->toBe(0);
    Storage::disk('public')->assertExists($session->source_path);
});

test('a user can create a session from a generated image', function () {
    $user = User::factory()->create();

    $generatedImage = GeneratedImage::create([
        'user_id' => $user->id,
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => GenerationStatus::Completed->value,
        'file_path' => 'generated-images/1.png',
        'file_url' => '/storage/generated-images/1.png',
        'batch_id' => fake()->uuid(),
    ]);

    $response = $this->actingAs($user)->post(route('image-editor.store'), [
        'source_type' => 'generated',
        'generated_image_id' => $generatedImage->id,
    ]);

    $response->assertRedirect();

    $session = $user->imageEditSessions()->first();
    expect($session)->not->toBeNull();
    expect($session->source_type)->toBe(ImageEditSourceType::Generated);
    expect($session->source_generated_image_id)->toBe($generatedImage->id);
});

test('source type is required', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('image-editor.store'), [])
        ->assertSessionHasErrors('source_type');
});

test('uploaded image must be a valid image file', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('image-editor.store'), [
        'source_type' => 'upload',
        'image' => UploadedFile::fake()->create('document.pdf', 100),
    ])->assertSessionHasErrors('image');
});

test('uploaded image must not exceed 10MB', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('image-editor.store'), [
        'source_type' => 'upload',
        'image' => UploadedFile::fake()->image('large.jpg')->size(11000),
    ])->assertSessionHasErrors('image');
});

test('user cannot select another users generated image', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $generatedImage = GeneratedImage::create([
        'user_id' => $owner->id,
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'credit_cost' => 10,
        'status' => GenerationStatus::Completed->value,
        'file_path' => 'generated-images/1.png',
        'file_url' => '/storage/generated-images/1.png',
        'batch_id' => fake()->uuid(),
    ]);

    $this->actingAs($other)->post(route('image-editor.store'), [
        'source_type' => 'generated',
        'generated_image_id' => $generatedImage->id,
    ])->assertSessionHasErrors('generated_image_id');
});
