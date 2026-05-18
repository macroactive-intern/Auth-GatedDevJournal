<?php

use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/journal', function () {
        return view('journal.index');
    })->name('journal.index');

    Route::resource('journal-entries', JournalEntryController::class);
    Route::post('/journal-entries/{journal_entry}/publish', [JournalEntryController::class, 'publish'])
        ->name('journal-entries.publish');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
