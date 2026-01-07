<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMomentRequest;
use App\Http\Requests\UpdateMomentRequest;
use App\Models\Moment;

class MomentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $moments = Moment::latest()->simplePaginate();

        return view('moments.index', [
            'moments' => $moments,
        ]);
    }
}
