<?php

use Illuminate\Support\Str;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

Artisan::command('commit', function () {
    if (file_exists(storage_path('app/COMMIT'))) {
        $contents = file_get_contents(storage_path('app/COMMIT'));

        info('Running commit for post: ' . $contents);

        $commit_message = 'New post: ' . Str::of($contents)->limit(37);

        // @TODO: Refactor to throw an exception
        $result = Process::path(base_path())->run('git add .');

        if ($result->failed()) {
            info('Commit failed [git add .]): ' . $result->errorOutput());
            return;
        }

        $result = Process::path(base_path())->run('git commit -m \'' . $commit_message . '\'');

        if ($result->failed()) {
            info('Commit failed [git commit -m]: ' . $result->errorOutput());
            return;
        }

        $result = Process::path(base_path())->run('git push');

        if ($result->failed()) {
            info('Commit failed [git push]: ' . $result->errorOutput());
            return;
        }

        unlink(storage_path('app/COMMIT'));
    }
});
