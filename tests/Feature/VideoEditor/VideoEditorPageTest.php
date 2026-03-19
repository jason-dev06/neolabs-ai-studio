<?php

use App\Models\User;
use App\Models\VideoEditSession;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('video-editor.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view the video editor index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('video-editor.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('video-editor/index')
        ->has('sessions')
        ->has('generatedVideos')
        ->has('credits')
        ->has('tools')
    );
});

test('authenticated users can view an edit session', function () {
    $user = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('video-editor.show', $session));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('video-editor/show')
        ->has('session')
        ->has('currentVideoUrl')
        ->has('credits')
        ->has('tools')
    );
});

test('users cannot view another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $session = VideoEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('video-editor.show', $session))
        ->assertForbidden();
});
