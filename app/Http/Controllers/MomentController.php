<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Moment;
use App\Http\Requests\StoreMomentRequest;
use App\Http\Requests\UpdateMomentRequest;

class MomentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = Page::where('slug', 'moments')
                    ->firstOrFail();

        $moments = Moment::latest()->simplePaginate(20);

        return view('moments.index', [
            'page' => $page,
            'metadata' => $page->metadata,
            'moments' => $moments,
        ]);
    }

    public function show(Moment $moment)
    {
        return view('moments.show', [
            'moment' => $moment,
            'metadata' => null,
        ]);
    }
}
