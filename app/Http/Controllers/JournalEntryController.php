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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', JournalEntry::class);

        return view('journal.index', [
            'entries' => $this->journalService->listForUser($request->user()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', JournalEntry::class);

        return view('journal.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        $entry = $this->journalService->create(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('journal-entries.show', $entry);
    }

    /**
     * Display the specified resource.
     */
    public function show(JournalEntry $journalEntry): View
    {
        $this->authorize('view', $journalEntry);

        return view('journal.show', [
            'entry' => $journalEntry->load('tags', 'user'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JournalEntry $journalEntry): View
    {
        $this->authorize('edit', $journalEntry);

        return view('journal.edit', [
            'entry' => $journalEntry->load('tags'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJournalEntryRequest $request, JournalEntry $journalEntry): RedirectResponse
    {
        $this->journalService->update($journalEntry, $request->validated());

        return redirect()->route('journal-entries.show', $journalEntry);
    }

    /**
     * Remove the specified resource from storage.
     */
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
}
