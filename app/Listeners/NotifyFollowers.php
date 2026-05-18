<?php

namespace App\Listeners;

use App\Events\JournalEntryPublished;
use Illuminate\Support\Facades\Log;

class NotifyFollowers
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(JournalEntryPublished $event): void
    {
        $journalEntry = $event->journalEntry->loadMissing('user');

        Log::channel('journal')->info('Journal entry published.', [
            'title' => $journalEntry->title,
            'author' => $journalEntry->user->name,
            'journal_entry_id' => $journalEntry->id,
            'published_at' => $journalEntry->published_at?->toISOString(),
        ]);
    }
}
