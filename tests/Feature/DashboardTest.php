<?php

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows an add new entry button on the dashboard for logged in users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Add new entry')
        ->assertSee(route('journal-entries.create'), false);
});

it('opens the create journal entry page for logged in users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('journal-entries.create'))
        ->assertOk()
        ->assertSee('Title')
        ->assertSee('Log')
        ->assertSee('Save entry')
        ->assertSee(route('journal-entries.store'), false);
});

it('renders the create page with validation errors', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession([
            'errors' => session()->get('errors', new Illuminate\Support\ViewErrorBag())->put(
                'default',
                validator(['tags' => [['not valid']]], ['tags.*' => ['string']])->errors()
            ),
        ])
        ->get(route('journal-entries.create'))
        ->assertOk()
        ->assertSee('The tags.0 field must be a string.');
});

it('shows public entries from all users on the dashboard feed', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    JournalEntry::factory()->public()->for($otherUser)->create([
        'title' => 'Public note from another user',
        'body' => 'This public dashboard feed body should be visible to logged in users.',
    ]);

    JournalEntry::factory()->for($otherUser)->create([
        'title' => 'Someone else private note',
    ]);

    JournalEntry::factory()->for($user)->create([
        'title' => 'My private dashboard note',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Public feed')
        ->assertSee('Public note from another user')
        ->assertSee('This public dashboard feed body')
        ->assertSee('By '.$otherUser->name)
        ->assertDontSee('My private dashboard note')
        ->assertDontSee('Someone else private note');
});
