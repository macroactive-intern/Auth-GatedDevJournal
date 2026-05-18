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

it('shows the logged in users saved entries on the dashboard feed', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    JournalEntry::factory()->for($user)->create([
        'title' => 'My saved dashboard note',
        'body' => 'This is the saved dashboard feed body that should be visible to the owner.',
    ]);

    JournalEntry::factory()->for($otherUser)->create([
        'title' => 'Someone else private note',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Saved entries')
        ->assertSee('My saved dashboard note')
        ->assertSee('This is the saved dashboard feed body')
        ->assertDontSee('Someone else private note');
});
