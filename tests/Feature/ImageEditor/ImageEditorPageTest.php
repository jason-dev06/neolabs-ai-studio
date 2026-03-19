<?php

use App\Models\ImageEditSession;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('image-editor.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view the image editor index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('image-editor.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('image-editor/index')
        ->has('sessions')
        ->has('generatedImages')
        ->has('credits')
        ->has('tools')
    );
});

test('authenticated users can view an edit session', function () {
    $user = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('image-editor.show', $session));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('image-editor/show')
        ->has('session')
        ->has('currentImageUrl')
        ->has('credits')
        ->has('tools')
    );
});

test('users cannot view another users session', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $session = ImageEditSession::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($other)
        ->get(route('image-editor.show', $session))
        ->assertForbidden();
});
