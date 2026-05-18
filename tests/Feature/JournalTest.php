<?php

use App\Models\JournalEntry;
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

it('only shows the logged in users entries on the journal page', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    JournalEntry::factory()->for($user)->create([
        'title' => 'My private journal note',
    ]);

    JournalEntry::factory()->public()->for($otherUser)->create([
        'title' => 'Other user public note',
    ]);

    $this->actingAs($user)
        ->get(route('journal.index'))
        ->assertOk()
        ->assertSee('My private journal note')
        ->assertDontSee('Other user public note');
});
