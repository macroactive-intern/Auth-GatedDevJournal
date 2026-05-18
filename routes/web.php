<?php

use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\JournalEntryFeedbackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
use App\Services\JournalService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (JournalService $journalService) {
    return view('dashboard', [
        'entries' => $journalService->listPublic(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/journal-entries/{journal_entry}', [JournalEntryController::class, 'show'])
    ->whereNumber('journal_entry')
    ->name('journal-entries.show');
Route::get('/shared/journal-entries/{journal_entry}', [JournalEntryController::class, 'sharedShow'])
    ->middleware('signed')
    ->name('journal-entries.shared.show');
Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
Route::get('/tags/{tag}', [TagController::class, 'show'])->name('tags.show');

Route::middleware('auth')->group(function () {
    Route::get('/journal', function (JournalService $journalService) {
        return view('journal.index', [
            'entries' => $journalService->listForUser(request()->user()),
        ]);
    })->name('journal.index');

    Route::resource('journal-entries', JournalEntryController::class)->except('show');
    Route::get('/journal-entries/{journal_entry}/share', [JournalEntryController::class, 'share'])
        ->name('journal-entries.share');
    Route::post('/journal-entries/{journal_entry}/publish', [JournalEntryController::class, 'publish'])
        ->name('journal-entries.publish');
    Route::get('/journal-entries/{journal_entry}/feedback', [JournalEntryFeedbackController::class, 'create'])
        ->whereNumber('journal_entry')
        ->name('journal-entries.feedback.create');
    Route::post('/journal-entries/{journal_entry}/feedback', [JournalEntryFeedbackController::class, 'store'])
        ->whereNumber('journal_entry')
        ->name('journal-entries.feedback.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
