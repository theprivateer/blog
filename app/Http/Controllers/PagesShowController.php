<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PagesShowController extends Controller
{
    public function __invoke(Page $page)
    {
        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
