<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\UpdateJournalEntryRequest;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(private readonly JournalService $journalService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', JournalEntry::class);
        $search = $request->string('search')->toString();

        return view('journal.index', [
            'entries' => $this->journalService->listForUser($request->user(), $search),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', JournalEntry::class);

        return view('journal.create');
    }

    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        $this->journalService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('dashboard');
    }

    public function show(JournalEntry $journalEntry): View
    {
        $this->authorize('view', $journalEntry);

        return view('journal.show', [
            'entry' => $this->journalService->getForDisplay($journalEntry),
        ]);
    }

    public function sharedShow(JournalEntry $journalEntry): View
    {
        return view('journal.show', [
            'entry' => $this->journalService->getForDisplay($journalEntry),
        ]);
    }

    public function edit(JournalEntry $journalEntry): View
    {
        $this->authorize('update', $journalEntry);

        return view('journal.edit', [
            'entry' => $this->journalService->getForEditing($journalEntry),
        ]);
    }

    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): RedirectResponse
    {
        $this->journalService->update($journalEntry, $request->validated());

        return redirect()->route('journal-entries.show', $journalEntry);
    }

    public function destroy(JournalEntry $journalEntry): RedirectResponse
    {
        $this->authorize('delete', $journalEntry);

        $this->journalService->delete($journalEntry);

        return redirect()->route('journal-entries.index');
    }

    public function publish(JournalEntry $journalEntry): RedirectResponse
    {
        $this->authorize('publish', $journalEntry);

        $this->journalService->publish($journalEntry);

        return redirect()->route('journal-entries.show', $journalEntry);
    }

    public function share(JournalEntry $journalEntry): View
    {
        $this->authorize('share', $journalEntry);

        return view('journal.share', [
            'entry' => $journalEntry,
            'shareUrl' => $this->journalService->temporaryShareUrl($journalEntry),
        ]);
    }
}
