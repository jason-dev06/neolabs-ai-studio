<?php

use App\Enums\GenerationStatus;
use App\Jobs\GenerateImageJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('prompt is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'number_of_images' => 1,
    ]);

    $response->assertSessionHasErrors('prompt');
});

test('prompt max length is 1000', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => str_repeat('a', 1001),
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'number_of_images' => 1,
    ]);

    $response->assertSessionHasErrors('prompt');
});

test('quality tier must be a valid enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => 'A test image',
        'quality_tier' => 'invalid',
        'aspect_ratio' => '1:1',
        'number_of_images' => 1,
    ]);

    $response->assertSessionHasErrors('quality_tier');
});

test('aspect ratio must be a valid enum', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '2:1',
        'number_of_images' => 1,
    ]);

    $response->assertSessionHasErrors('aspect_ratio');
});

test('number of images must be between 1 and 4', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'number_of_images' => 5,
    ]);

    $response->assertSessionHasErrors('number_of_images');
});

test('insufficient credits are rejected', function () {
    $user = User::factory()->create(['credits' => 5]);

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => 'A test image',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'number_of_images' => 1,
    ]);

    $response->assertSessionHasErrors('credits');
});

test('successful generation creates pending records and deducts credits', function () {
    $user = User::factory()->create(['credits' => 100]);

    $response = $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => 'A beautiful sunset',
        'quality_tier' => 'basic',
        'aspect_ratio' => '1:1',
        'number_of_images' => 2,
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->credits)->toBe(80);
    expect($user->generatedImages)->toHaveCount(2);

    $image = $user->generatedImages->first();
    expect($image->status)->toBe(GenerationStatus::Pending);
    expect($image->prompt)->toBe('A beautiful sunset');
    expect($image->credit_cost)->toBe(10);

    Queue::assertPushed(GenerateImageJob::class, 2);
});

test('all images in a batch share the same batch id', function () {
    $user = User::factory()->create(['credits' => 200]);

    $this->actingAs($user)->post(route('image-generator.store'), [
        'prompt' => 'A test batch',
        'quality_tier' => 'smart',
        'aspect_ratio' => '16:9',
        'number_of_images' => 3,
    ]);

    $batchIds = $user->generatedImages()->pluck('batch_id')->unique();
    expect($batchIds)->toHaveCount(1);
});
