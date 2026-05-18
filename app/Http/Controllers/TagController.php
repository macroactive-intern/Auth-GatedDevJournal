<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        return view('tags.index', [
            'tags' => Tag::query()
                ->withCount('journalEntries')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Tag $tag): View
    {
        return view('tags.show', [
            'tag' => $tag->loadCount('journalEntries'),
            'entries' => $tag->journalEntries()
                ->where('is_public', true)
                ->with('user', 'tags')
                ->latest('published_at')
                ->get(),
        ]);
    }
}
