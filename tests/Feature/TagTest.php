<?php

use App\Models\JournalEntry;
use App\Models\Tag;
use App\Models\User;


it('shows tags with entry counts on the tags page', function () {
    $tag = Tag::create(['name' => 'laravel']);
    $entry = JournalEntry::factory()->for(User::factory())->create(['is_public' => true]);

    $entry->tags()->attach($tag);

    $this->get('/tags')
        ->assertOk()
        ->assertSee('laravel')
        ->assertSee('1');
});

it('only shows public entries on a tag page', function () {
    $tag = Tag::create(['name' => 'php']);
    $publicEntry = JournalEntry::factory()->for(User::factory())->create([
        'title' => 'Public entry',
        'is_public' => true,
    ]);
    $privateEntry = JournalEntry::factory()->for(User::factory())->create([
        'title' => 'Private entry',
        'is_public' => false,
    ]);

    $tag->journalEntries()->attach([$publicEntry->id, $privateEntry->id]);

    $this->get(route('tags.show', $tag))
        ->assertOk()
        ->assertSee('Public entry')
        ->assertDontSee('Private entry');
});
