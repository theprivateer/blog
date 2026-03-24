<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $page = Page::where('slug', 'notes')
            ->firstOrFail();

        $notes = Note::latest()->simplePaginate();

        return view('notes.index', [
            'page' => $page,
            'metadata' => $page->metadata,
            'notes' => $notes,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): View
    {
        return view('notes.show', [
            'note' => $note,
            'metadata' => null,
        ]);
    }
}
