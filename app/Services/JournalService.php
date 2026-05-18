<?php

namespace App\Services;

use App\Events\JournalEntryPublished;
use App\Models\JournalEntry;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public function listForUser(User $user): Collection
    {
        return $user->journalEntries()
            ->with('tags')
            ->latest()
            ->get();
    }

    public function create(User $user, array $data): JournalEntry
    {
        return DB::transaction(function () use ($user, $data): JournalEntry {
            $tagNames = Arr::pull($data, 'tags', []);
            $data['is_public'] = (bool) ($data['is_public'] ?? false);

            if ($data['is_public'] && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $entry = $user->journalEntries()->create($data);
            $this->syncTags($entry, $tagNames);

            if ($entry->is_public) {
                JournalEntryPublished::dispatch($entry);
            }

            return $entry;
        });
    }

    public function update(JournalEntry $entry, array $data): JournalEntry
    {
        return DB::transaction(function () use ($entry, $data): JournalEntry {
            $tagNames = Arr::pull($data, 'tags', null);
            $wasPublic = $entry->is_public;

            if (array_key_exists('is_public', $data)) {
                $data['is_public'] = (bool) $data['is_public'];
            }

            if (! $wasPublic && ($data['is_public'] ?? false) && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $entry->update($data);

            if (is_array($tagNames)) {
                $this->syncTags($entry, $tagNames);
            }

            if (! $wasPublic && $entry->is_public) {
                JournalEntryPublished::dispatch($entry);
            }

            return $entry->refresh();
        });
    }

    public function publish(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry): JournalEntry {
            $wasPublic = $entry->is_public;

            $entry->forceFill([
                'is_public' => true,
                'published_at' => $entry->published_at ?? now(),
            ])->save();

            if (! $wasPublic) {
                JournalEntryPublished::dispatch($entry);
            }

            return $entry->refresh();
        });
    }

    public function delete(JournalEntry $entry): void
    {
        $entry->delete();
    }

    private function syncTags(JournalEntry $entry, array $tagNames): void
    {
        $tagIds = collect($tagNames)
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name): int => Tag::firstOrCreate(['name' => $name])->id)
            ->all();

        $entry->tags()->sync($tagIds);
    }
}
