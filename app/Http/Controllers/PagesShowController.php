<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;

class PagesShowController extends Controller
{
    public function __invoke(Page $page): View
    {
        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
