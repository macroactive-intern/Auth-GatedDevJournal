<?php

namespace App\Services;

use App\Events\JournalEntryPublished;
use App\Models\JournalEntry;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JournalService
{
    private const DAILY_ENTRY_LIMIT = 10;

    public function listForUser(User $user, ?string $search = null): Collection
    {
        return $user->journalEntries()
            ->with('tags')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();
    }

    public function listPublic(?string $search = null): Collection
    {
        return JournalEntry::query()
            ->with('tags', 'user')
            ->withCount('feedback')
            ->where('is_public', true)
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('tags', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('published_at')
            ->latest()
            ->get();
    }

    public function getForDisplay(JournalEntry $entry): JournalEntry
    {
        return $entry->load('tags', 'user');
    }

    public function getForEditing(JournalEntry $entry): JournalEntry
    {
        return $entry->load('tags');
    }

    public function temporaryShareUrl(JournalEntry $entry): string
    {
        return URL::temporarySignedRoute(
            name: 'journal-entries.shared.show',
            expiration: now()->addDays(7),
            parameters: ['journal_entry' => $entry]
        );
    }

    public function create(User $user, array $data): JournalEntry
    {
        $this->enforceDailyEntryLimit($user);

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
            $previousBody = $entry->body;

            if (array_key_exists('is_public', $data)) {
                $data['is_public'] = (bool) $data['is_public'];
            }

            if (! $wasPublic && ($data['is_public'] ?? false) && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            $entry->update($data);

            if (array_key_exists('body', $data) && $data['body'] !== $previousBody) {
                $entry->revisions()->create([
                    'body' => $previousBody,
                ]);
            }

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
            $lockedEntry = JournalEntry::query()
                ->whereKey($entry->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEntry->is_public) {
                return $lockedEntry->refresh();
            }

            $lockedEntry->forceFill([
                'is_public' => true,
                'published_at' => $lockedEntry->published_at ?? now(),
            ])->save();

            JournalEntryPublished::dispatch($lockedEntry);

            return $lockedEntry->refresh();
        });
    }

    public function delete(JournalEntry $entry): void
    {
        DB::transaction(function () use ($entry): void {
            $entry->delete();
        });
    }

    public function attachTags(JournalEntry $entry, array $tagNames): JournalEntry
    {
        return DB::transaction(function () use ($entry, $tagNames): JournalEntry {
            $entry->tags()->syncWithoutDetaching($this->tagIdsFor($tagNames));

            return $entry->load('tags');
        });
    }

    private function syncTags(JournalEntry $entry, array $tagNames): void
    {
        $entry->tags()->sync($this->tagIdsFor($tagNames));
    }

    private function tagIdsFor(array $tagNames): array
    {
        return collect($tagNames)
            ->map(fn (string $name): string => trim($name))
            ->filter()
            ->unique()
            ->map(fn (string $name): int => Tag::firstOrCreate(['name' => $name])->id)
            ->all();
    }

    private function enforceDailyEntryLimit(User $user): void
    {
        $now = CarbonImmutable::now('UTC');
        $startOfDay = $now->startOfDay();
        $resetAt = $startOfDay->addDay();
        $key = sprintf('journal-entry-limit:%s:%s', $user->id, $startOfDay->toDateString());
        $secondsUntilReset = max(1, $now->diffInSeconds($resetAt));

        Cache::add($key, 0, $secondsUntilReset);

        $entriesCreatedToday = Cache::increment($key);

        if ($entriesCreatedToday <= self::DAILY_ENTRY_LIMIT) {
            return;
        }

        throw new HttpException(
            statusCode: 429,
            message: sprintf(
                'Daily journal entry limit exceeded. You can create more entries after %s.',
                $resetAt->toIso8601String()
            ),
            headers: [
                'Retry-After' => (string) $secondsUntilReset,
                'X-RateLimit-Limit' => (string) self::DAILY_ENTRY_LIMIT,
                'X-RateLimit-Reset' => $resetAt->toIso8601String(),
            ],
        );
    }
}
