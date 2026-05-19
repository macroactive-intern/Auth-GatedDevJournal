<?php

use App\Models\JournalEntry;
use App\Models\JournalEntryFeedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests from the journal page', function () {
    $this->get(route('journal-entries.index'))->assertRedirect('/login');
});

it('allows authenticated users to view the journal page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('journal-entries.index'))
        ->assertOk()
        ->assertSee('My Journal');
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
        ->get(route('journal-entries.index'))
        ->assertOk()
        ->assertSee('This is your personal journal feed')
        ->assertSee('My private journal note')
        ->assertSee('Edit')
        ->assertDontSee('Other user public note');
});

it('shows a feedback button for the users public entries on the journal page', function () {
    $user = User::factory()->create();

    $publicEntry = JournalEntry::factory()->public()->for($user)->create([
        'title' => 'Public entry with feedback button',
    ]);

    JournalEntry::factory()->for($user)->create([
        'title' => 'Private entry with publish button',
    ]);

    $this->actingAs($user)
        ->get(route('journal-entries.index'))
        ->assertOk()
        ->assertSee('Public entry with feedback button')
        ->assertSee('View feedback')
        ->assertSee(route('journal-entries.feedback.create', $publicEntry), false)
        ->assertSee('Private entry with publish button')
        ->assertSee('Publish');
});

it('lets entry owners view feedback on their public entries', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create(['name' => 'Feedback Reader']);
    $entry = JournalEntry::factory()->public()->for($owner)->create([
        'title' => 'Owned public entry with feedback',
    ]);

    JournalEntryFeedback::query()->create([
        'journal_entry_id' => $entry->id,
        'user_id' => $commenter->id,
        'body' => 'This feedback should be visible to the entry owner.',
    ]);

    $this->actingAs($owner)
        ->get(route('journal-entries.feedback.create', $entry))
        ->assertOk()
        ->assertSee('Entry Feedback')
        ->assertSee('This is your entry. Feedback from other users appears below.')
        ->assertSee('Feedback Reader')
        ->assertSee('This feedback should be visible to the entry owner.')
        ->assertDontSee('Send feedback');
});

it('shows an edit form for the owners journal entry', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create([
        'title' => 'Editable journal title',
        'body' => journalTestBody('original'),
    ]);

    $this->actingAs($user)
        ->get(route('journal-entries.edit', $entry))
        ->assertOk()
        ->assertSee('Editable journal title')
        ->assertSee('Log')
        ->assertSee('Save changes')
        ->assertSee(route('journal-entries.update', $entry), false);
});

it('lets owners update their entries from the edit form', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create();

    $this->actingAs($user)
        ->patch(route('journal-entries.update', $entry), [
            'title' => 'Updated from journal edit form',
            'body' => journalTestBody('updated'),
            'tags' => ['laravel', '', 'journal'],
            'is_public' => '1',
        ])
        ->assertRedirect(route('journal-entries.show', $entry));

    $entry->refresh();

    expect($entry->title)->toBe('Updated from journal edit form')
        ->and($entry->is_public)->toBeTrue()
        ->and($entry->tags()->pluck('name')->all())->toBe(['laravel', 'journal']);
});

function journalTestBody(string $prefix): string
{
    return collect(range(1, 50))
        ->map(fn (int $number): string => "{$prefix}{$number}")
        ->implode(' ');
}
