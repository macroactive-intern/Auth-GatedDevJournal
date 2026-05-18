<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_tags_page_shows_tags_with_entry_counts(): void
    {
        $tag = Tag::create(['name' => 'laravel']);
        $entry = JournalEntry::factory()->for(User::factory())->create(['is_public' => true]);

        $entry->tags()->attach($tag);

        $this->get('/tags')
            ->assertOk()
            ->assertSee('laravel')
            ->assertSee('1');
    }

    public function test_tag_page_only_shows_public_entries(): void
    {
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
    }
}
