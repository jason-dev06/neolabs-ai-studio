<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('video-generator.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view the video generator page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('video-generator.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('video-generator/index')
        ->has('videos')
        ->has('credits')
    );
});
