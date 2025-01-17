<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Spatie\Sheets\Facades\Sheets;

class SlashesIndexController extends Controller
{
    public function __invoke(): View
    {
        $slashes = Sheets::collection('slashes')
                    ->all()
                    ->filter(function ($slash) {
                        return ! $slash->draft && strpos($slash->slug, '/') === false;
                    });

        return view('slashes.index', [
           'slashes' => $slashes,
        ]);
    }
}
