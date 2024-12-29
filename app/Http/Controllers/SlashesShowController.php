<?php

namespace App\Http\Controllers;

use App\Models\Slash;
use TOC\TocGenerator;
use Illuminate\Contracts\View\View;

class SlashesShowController extends Controller
{
    public function __invoke(Slash $slash): View
    {
        $toc = (new TocGenerator)->getHtmlMenu($slash->contents);

        return view('slashes.show', [
            'slash' => $slash,
            'toc' => $toc,
        ]);
    }
}
