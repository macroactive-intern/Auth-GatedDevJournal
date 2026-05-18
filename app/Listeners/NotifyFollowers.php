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
        Log::info('Journal entry published.', [
            'journal_entry_id' => $event->journalEntry->id,
            'user_id' => $event->journalEntry->user_id,
            'published_at' => $event->journalEntry->published_at?->toISOString(),
        ]);
    }
}
