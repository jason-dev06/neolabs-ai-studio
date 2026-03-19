<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('image-generator.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view the image generator page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('image-generator.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('image-generator/index')
        ->has('images')
        ->has('credits')
    );
});
