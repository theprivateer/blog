<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Spatie\Sheets\Facades\Sheets;

class SlashesIndexController extends Controller
{
    public function __invoke(): View
    {
        $slashes = Sheets::collection('slashes')
                    ->all();
                    // ->sortBy('date');

        return view('slashes.index', [
           'slashes' => $slashes,
        ]);
    }
}
