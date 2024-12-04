<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sheets\Facades\Sheets;

class PagesShowController extends Controller
{
    public function __invoke($slug)
    {
        $page = Sheets::collection('pages')
                    ->all()
                    ->where('slug', $slug)
                    ->first();

        abort_if(! $page, 404);

        return view('pages.show', [
            'page' => $page,
        ]);
    }
}
