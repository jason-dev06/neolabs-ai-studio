<?php

use App\Models\ImageEditSession;
use App\Models\ImageEditStep;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('a user can delete their own session', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    ImageEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 1,
    ]);

    $this->actingAs($user)
        ->delete(route('image-editor.destroy', $session))
        ->assertRedirect(route('image-editor.index'));

    $this->assertDatabaseMissing('image_edit_sessions', ['id' => $session->id]);
    $this->assertDatabaseMissing('image_edit_steps', ['session_id' => $session->id]);
});

test('a user cannot delete another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->delete(route('image-editor.destroy', $session))
        ->assertForbidden();

    $this->assertDatabaseHas('image_edit_sessions', ['id' => $session->id]);
});

test('guests cannot delete sessions', function () {
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $this->delete(route('image-editor.destroy', $session))
        ->assertRedirect(route('login'));
});
