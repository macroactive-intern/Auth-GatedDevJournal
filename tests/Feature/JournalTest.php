<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests from the journal page', function () {
    $this->get('/journal')->assertRedirect('/login');
});

it('allows authenticated users to view the journal page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/journal')
        ->assertOk()
        ->assertSee('Dev Journal');
});
