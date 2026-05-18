<?php

use App\Models\JournalEntry;
use App\Models\JournalEntryFeedback;
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
        ->assertSee('Give feedback')
        ->assertDontSee('My private dashboard note')
        ->assertDontSee('Someone else private note');
});

it('shows feedback counts on public dashboard entries', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $entry = JournalEntry::factory()->public()->for($otherUser)->create([
        'title' => 'Public note with feedback',
    ]);

    JournalEntryFeedback::query()->create([
        'journal_entry_id' => $entry->id,
        'user_id' => $user->id,
        'body' => 'This is useful feedback.',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Public note with feedback')
        ->assertSee('Give feedback')
        ->assertSee('(1)');
});

it('stores feedback from another user on a public entry', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $entry = JournalEntry::factory()->public()->for($owner)->create();

    $this->actingAs($commenter)
        ->post(route('journal-entries.feedback.store', $entry), [
            'body' => 'This log was helpful and gave me a useful direction.',
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('journal_entry_feedback', [
        'journal_entry_id' => $entry->id,
        'user_id' => $commenter->id,
        'body' => 'This log was helpful and gave me a useful direction.',
    ]);
});

it('shows existing feedback when opening the feedback page', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create(['name' => 'Existing Commenter']);
    $viewer = User::factory()->create();
    $entry = JournalEntry::factory()->public()->for($owner)->create([
        'title' => 'Entry with visible feedback',
    ]);

    JournalEntryFeedback::query()->create([
        'journal_entry_id' => $entry->id,
        'user_id' => $commenter->id,
        'body' => 'This previous feedback should show under the form.',
    ]);

    $this->actingAs($viewer)
        ->get(route('journal-entries.feedback.create', $entry))
        ->assertOk()
        ->assertSee('Entry with visible feedback')
        ->assertSee('Existing Commenter')
        ->assertSee('This previous feedback should show under the form.')
        ->assertSee('Send feedback');
});

it('shows an empty feedback message when an entry has no feedback yet', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $entry = JournalEntry::factory()->public()->for($owner)->create();

    $this->actingAs($viewer)
        ->get(route('journal-entries.feedback.create', $entry))
        ->assertOk()
        ->assertSee('No feedback has been added yet.');
});

it('prevents owners from leaving feedback on their own entries', function () {
    $owner = User::factory()->create();
    $entry = JournalEntry::factory()->public()->for($owner)->create();

    $this->actingAs($owner)
        ->get(route('journal-entries.feedback.create', $entry))
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('journal-entries.feedback.store', $entry), [
            'body' => 'Commenting on my own entry is not allowed.',
        ])
        ->assertForbidden();
});
