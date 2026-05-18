<?php

namespace App\Providers;

use App\Events\JournalEntryPublished;
use App\Listeners\NotifyFollowers;
use App\Models\JournalEntry;
use App\Policies\JournalEntryPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);

        Event::listen(JournalEntryPublished::class, NotifyFollowers::class);
    }
}
