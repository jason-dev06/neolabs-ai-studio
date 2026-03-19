<?php

use App\Models\User;
use App\Models\VideoEditSession;
use App\Models\VideoEditStep;
use Illuminate\Support\Facades\Storage;

test('a user can delete their own session', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    VideoEditStep::factory()->completed()->create([
        'session_id' => $session->id,
        'step_number' => 1,
    ]);

    $this->actingAs($user)
        ->delete(route('video-editor.destroy', $session))
        ->assertRedirect(route('video-editor.index'));

    $this->assertDatabaseMissing('video_edit_sessions', ['id' => $session->id]);
    $this->assertDatabaseMissing('video_edit_steps', ['session_id' => $session->id]);
});

test('a user cannot delete another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->delete(route('video-editor.destroy', $session))
        ->assertForbidden();

    $this->assertDatabaseHas('video_edit_sessions', ['id' => $session->id]);
});

test('guests cannot delete sessions', function () {
    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $this->delete(route('video-editor.destroy', $session))
        ->assertRedirect(route('login'));
});
