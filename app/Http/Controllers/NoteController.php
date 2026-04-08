<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Services\SiteManager;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $site = app(SiteManager::class)->siteForRequest();

        $listingPage = Page::query()
            ->forSite($site)
            ->where('slug', 'notes')
            ->firstOrFail();

        $notes = Note::query()
            ->forSite($site)
            ->latest()
            ->simplePaginate();

        return view('notes.index', [
            'listingPage' => $listingPage,
            'metadata' => $listingPage->metadata,
            'notes' => $notes,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $note): View
    {
        $note = Note::query()
            ->forSite(app(SiteManager::class)->siteForRequest())
            ->findOrFail($note);

        return view('notes.show', [
            'note' => $note,
            'metadata' => null,
        ]);
    }
}
