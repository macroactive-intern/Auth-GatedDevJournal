<?php

use App\Events\JournalEntryPublished;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('allows a user to create an entry', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('journal-entries.store'), [
        'title' => 'My first dev journal entry',
        'body' => journalBody(),
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('journal_entries', [
        'user_id' => $user->id,
        'title' => 'My first dev journal entry',
    ]);
});

it('returns 429 for the eleventh entry in a day', function () {
    $user = User::factory()->create();

    Cache::flush();

    foreach (range(1, 10) as $entryNumber) {
        $this->actingAs($user)
            ->post(route('journal-entries.store'), [
                'title' => "Entry {$entryNumber}",
                'body' => journalBody(),
            ])
            ->assertRedirect(route('dashboard'));
    }

    $this->actingAs($user)
        ->post(route('journal-entries.store'), [
            'title' => 'One too many',
            'body' => journalBody(),
        ])
        ->assertStatus(429)
        ->assertHeader('X-RateLimit-Limit', '10')
        ->assertHeader('X-RateLimit-Reset');
});

it('allows an owner to edit their own entry', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('journal-entries.edit', $entry))
        ->assertOk()
        ->assertSee($entry->title);

    $this->actingAs($user)
        ->patch(route('journal-entries.update', $entry), [
            'title' => 'Updated title',
            'body' => journalBody(),
        ])
        ->assertRedirect(route('journal-entries.show', $entry));

    $this->assertDatabaseHas('journal_entries', [
        'id' => $entry->id,
        'title' => 'Updated title',
    ]);
});

it('stores the previous body as a revision before updating an entry', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create([
        'body' => 'Original body content before the edit.',
    ]);

    $this->actingAs($user)
        ->patch(route('journal-entries.update', $entry), [
            'title' => $entry->title,
            'body' => journalBody(),
        ])
        ->assertRedirect(route('journal-entries.show', $entry));

    $this->assertDatabaseHas('journal_entry_revisions', [
        'journal_entry_id' => $entry->id,
        'body' => 'Original body content before the edit.',
    ]);

    expect($entry->fresh()->revisions)->toHaveCount(1);
});

it('prevents other users from editing an entry', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $entry = JournalEntry::factory()->for($owner)->create();

    $this->actingAs($otherUser)
        ->get(route('journal-entries.edit', $entry))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->patch(route('journal-entries.update', $entry), [
            'title' => 'Not allowed',
            'body' => journalBody(),
        ])
        ->assertForbidden();
});

it('shows public entries publicly', function () {
    $entry = JournalEntry::factory()->public()->create([
        'title' => 'Public dev note',
    ]);

    $this->get(route('journal-entries.show', $entry))
        ->assertOk()
        ->assertSee('Public dev note');
});

it('hides private entries publicly', function () {
    $entry = JournalEntry::factory()->create([
        'title' => 'Private dev note',
        'is_public' => false,
    ]);

    $this->get(route('journal-entries.show', $entry))
        ->assertForbidden();
});

it('allows private entries to be viewed with a valid signed share URL', function () {
    $entry = JournalEntry::factory()->create([
        'title' => 'Signed private note',
        'is_public' => false,
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'journal-entries.shared.show',
        now()->addDays(7),
        ['journal_entry' => $entry]
    );

    $this->get($signedUrl)
        ->assertOk()
        ->assertSee('Signed private note');
});

it('rejects private entry share URLs without a valid signature', function () {
    $entry = JournalEntry::factory()->create([
        'title' => 'Unsigned private note',
        'is_public' => false,
    ]);

    $this->get(route('journal-entries.shared.show', $entry))
        ->assertForbidden();
});

it('allows owners to generate a seven day signed share URL', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create(['is_public' => false]);

    $response = $this->actingAs($user)
        ->get(route('journal-entries.share', $entry))
        ->assertOk()
        ->assertSee('This signed link is valid for 7 days.');

    $content = $response->getContent();

    expect($content)->toContain('signature=')
        ->and($content)->toContain('expires=');
});

it('searches a user journal entries', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->for($user)->create(['title' => 'Laravel service container']);
    JournalEntry::factory()->for($user)->create(['title' => 'Unrelated note']);

    $this->actingAs($user)
        ->get(route('journal-entries.index', ['search' => 'service']))
        ->assertOk()
        ->assertSee('Search my journal')
        ->assertSee('Laravel service container')
        ->assertDontSee('Unrelated note');
});

it('shows an empty state when no user journal entries match search', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->for($user)->create(['title' => 'Laravel service container']);

    $this->actingAs($user)
        ->get(route('journal-entries.index', ['search' => 'missing']))
        ->assertOk()
        ->assertSee('No journal entries match your search.')
        ->assertDontSee('Laravel service container');
});

it('dispatches an event when publishing an entry', function () {
    Event::fake([JournalEntryPublished::class]);

    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create(['is_public' => false]);

    $this->actingAs($user)
        ->post(route('journal-entries.publish', $entry))
        ->assertRedirect(route('journal-entries.show', $entry));

    Event::assertDispatched(JournalEntryPublished::class, function (JournalEntryPublished $event) use ($entry): bool {
        return $event->journalEntry->is($entry);
    });
});

it('does not dispatch a publishing event when the entry is already public', function () {
    Event::fake([JournalEntryPublished::class]);

    $user = User::factory()->create();
    $entry = JournalEntry::factory()->public()->for($user)->create();

    $this->actingAs($user)
        ->post(route('journal-entries.publish', $entry))
        ->assertRedirect(route('journal-entries.show', $entry));

    Event::assertNotDispatched(JournalEntryPublished::class);
});

it('attaches tags correctly', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('journal-entries.store'), [
        'title' => 'Tagged entry',
        'body' => journalBody(),
        'tags' => ['laravel', 'testing'],
    ]);

    $entry = JournalEntry::firstOrFail();

    $this->assertDatabaseHas('tags', ['name' => 'laravel']);
    $this->assertDatabaseHas('tags', ['name' => 'testing']);
    expect($entry->tags()->where('name', 'laravel')->exists())->toBeTrue();
    expect($entry->tags()->where('name', 'testing')->exists())->toBeTrue();
});

function journalBody(): string
{
    return collect(range(1, 50))
        ->map(fn (int $number): string => "word{$number}")
        ->implode(' ');
}
