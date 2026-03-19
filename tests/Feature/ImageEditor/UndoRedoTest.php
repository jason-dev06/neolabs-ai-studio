<?php

use App\Models\ImageEditSession;
use App\Models\ImageEditStep;
use App\Models\User;

test('undo decrements current step', function () {
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create([
        'user_id' => $user->id,
        'current_step' => 2,
    ]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 1,
    ]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 2,
    ]);

    $this->actingAs($user)->post(route('image-editor.undo', $session))
        ->assertRedirect();

    $session->refresh();
    expect($session->current_step)->toBe(1);
});

test('undo at step 0 stays at 0', function () {
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create([
        'user_id' => $user->id,
        'current_step' => 0,
    ]);

    $this->actingAs($user)->post(route('image-editor.undo', $session))
        ->assertRedirect();

    $session->refresh();
    expect($session->current_step)->toBe(0);
});

test('redo increments current step', function () {
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create([
        'user_id' => $user->id,
        'current_step' => 1,
    ]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 1,
    ]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 2,
    ]);

    $this->actingAs($user)->post(route('image-editor.redo', $session))
        ->assertRedirect();

    $session->refresh();
    expect($session->current_step)->toBe(2);
});

test('redo at max step stays at max', function () {
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create([
        'user_id' => $user->id,
        'current_step' => 2,
    ]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 1,
    ]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 2,
    ]);

    $this->actingAs($user)->post(route('image-editor.redo', $session))
        ->assertRedirect();

    $session->refresh();
    expect($session->current_step)->toBe(2);
});

test('user cannot undo another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)->post(route('image-editor.undo', $session))
        ->assertForbidden();
});
