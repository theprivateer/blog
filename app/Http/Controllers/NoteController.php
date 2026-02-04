<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Page;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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
    public function show(Note $note)
    {
        return view('notes.show', [
            'note' => $note,
            'metadata' => null,
        ]);
    }
}
