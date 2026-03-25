<?php

namespace Privateer\Basecms\Http\Controllers;

use Illuminate\Contracts\View\View;
use Privateer\Basecms\Models\Page;

class PageController extends Controller
{
    /**
     * Display homepage.
     */
    public function index(): View
    {
        $page = Page::where('is_homepage', true)
            ->firstOrFail();

        return view((string) config('basecms.views.pages.index', 'pages.index'), [
            'page' => $page,
            'metadata' => $page->metadata,
        ]);
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page): View
    {
        if ($page->draft) {
            abort(404);
        }

        return view($page->template ?: config('basecms.views.pages.show', 'pages.show'), [
            'page' => $page,
            'metadata' => $page->metadata,
        ]);
    }
}
