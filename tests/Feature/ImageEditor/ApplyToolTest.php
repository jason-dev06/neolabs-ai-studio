<?php

use App\Enums\GenerationStatus;
use App\Jobs\ProcessImageEditJob;
use App\Models\ImageEditSession;
use App\Models\ImageEditStep;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('a user can apply a tool to their session', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'enhance',
    ]);

    $response->assertRedirect();

    $user->refresh();
    expect($user->credits)->toBe(95); // enhance costs 5

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->step_number)->toBe(1);
    expect($step->status)->toBe(GenerationStatus::Pending);
    expect($step->credit_cost)->toBe(5);

    Queue::assertPushed(ProcessImageEditJob::class);
});

test('tool must be a valid enum', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'invalid_tool',
    ])->assertSessionHasErrors('tool');
});

test('insufficient credits are rejected', function () {
    $user = User::factory()->create(['credits' => 2]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'enhance',
    ])->assertSessionHasErrors('credits');

    Queue::assertNothingPushed();
});

test('inpaint tool requires a prompt setting', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'inpaint',
    ])->assertSessionHasErrors('tool_settings.prompt');
});

test('style transfer requires a style setting', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'style_transfer',
    ])->assertSessionHasErrors('tool_settings.style');
});

test('erase_object tool requires erase_prompt setting', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'erase_object',
    ])->assertSessionHasErrors('tool_settings.erase_prompt');
});

test('erase_object tool works with erase_prompt', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'erase_object',
        'tool_settings' => ['erase_prompt' => 'the person in the background'],
    ]);

    $response->assertRedirect();

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->tool->value)->toBe('erase_object');
    expect($step->tool_settings['erase_prompt'])->toBe('the person in the background');

    Queue::assertPushed(ProcessImageEditJob::class);
});

test('upscale tool works with scale_factor', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'upscale',
        'tool_settings' => ['scale_factor' => 4],
    ]);

    $response->assertRedirect();

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->tool->value)->toBe('upscale');

    Queue::assertPushed(ProcessImageEditJob::class);
});

test('style_transfer accepts oil_painting style', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'style_transfer',
        'tool_settings' => ['style' => 'oil_painting'],
    ]);

    $response->assertRedirect();

    $step = $session->steps()->first();
    expect($step)->not->toBeNull();
    expect($step->tool_settings['style'])->toBe('oil_painting');

    Queue::assertPushed(ProcessImageEditJob::class);
});

test('style_transfer accepts pixel_art style', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'style_transfer',
        'tool_settings' => ['style' => 'pixel_art'],
    ]);

    $response->assertRedirect();

    Queue::assertPushed(ProcessImageEditJob::class);
});

test('cannot apply tool while a step is processing', function () {
    $user = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    ImageEditStep::factory()->create([
        'session_id' => $session->id,
        'step_number' => 1,
        'status' => GenerationStatus::Processing,
    ]);

    $this->actingAs($user)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'enhance',
    ])->assertSessionHasErrors('tool');
});

test('user cannot apply tool to another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create(['credits' => 100]);
    $session = ImageEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->post(route('image-editor.apply-tool', $session), [
        'tool' => 'enhance',
    ])->assertForbidden();
});
