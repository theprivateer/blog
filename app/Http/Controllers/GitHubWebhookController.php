<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GitHubWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        info($request->getContent());

        return;
    }
}
