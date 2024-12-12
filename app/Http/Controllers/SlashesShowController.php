<?php

namespace App\Http\Controllers;

use App\Models\Slash;
use Illuminate\Contracts\View\View;

class SlashesShowController extends Controller
{
    public function __invoke(Slash $slash): View
    {
        return view('slashes.show', [
            'slash' => $slash,
        ]);
    }
}
