<?php

namespace App\Http\Controllers;

use App\Models\Slash;
use TOC\TocGenerator;
use Illuminate\Contracts\View\View;
use Spatie\Sheets\Facades\Sheets;

class SlashesShowController extends Controller
{
    public function __invoke(Slash $slash): View
    {
        $toc = (new TocGenerator)->getHtmlMenu($slash->contents);

        $tree = explode('/', $slash->slug);

        if (count($tree) > 1) {
            $parent = Sheets::collection('slashes')->get($tree[0]);
        }

        return view('slashes.show', [
            'slash' => $slash,
            'toc' => $toc,
            'parent' => $parent ?? null,
        ]);
    }
}
