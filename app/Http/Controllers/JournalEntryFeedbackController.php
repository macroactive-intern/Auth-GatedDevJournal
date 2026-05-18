<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJournalEntryFeedbackRequest;
use App\Models\JournalEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalEntryFeedbackController extends Controller
{
    public function create(Request $request, JournalEntry $journalEntry): View
    {
        abort_unless($journalEntry->is_public, 404);
        abort_if($request->user()->is($journalEntry->user), 403);

        return view('journal.feedback.create', [
            'entry' => $journalEntry->load([
                'user',
                'feedback' => fn ($query) => $query->with('user')->latest(),
            ]),
        ]);
    }

    public function store(StoreJournalEntryFeedbackRequest $request, JournalEntry $journalEntry): RedirectResponse
    {
        $journalEntry->feedback()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Feedback sent.');
    }
}
