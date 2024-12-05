<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Process\Pipe;
use Illuminate\Process\ProcessResult;
use Illuminate\Support\Facades\Process;

class GitHubWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        // TODO: Authorization logic
        $push = json_decode($request->getContent());

        if ($push->ref == 'refs/heads/main'
            && $push->repository->full_name == 'theprivateer/blog') {
                info('Running git pull');
                $result = Process::path(base_path())->run('php vendor/bin/envoy run deploy');
                info($result->output());
        }

        return;
    }
}
