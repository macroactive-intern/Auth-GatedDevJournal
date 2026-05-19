<?php

use App\Models\User;


it('renders the confirm password screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/confirm-password')
        ->assertOk();
});

it('confirms a password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/confirm-password', [
            'password' => 'password',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

it('does not confirm an invalid password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/confirm-password', [
            'password' => 'wrong-password',
        ])
        ->assertSessionHasErrors();
});
