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

        Process::path(base_path())->run('git add .');
        Process::path(base_path())->run('git commit -m \'' . $commit_message . '\'');
        Process::path(base_path())->run('git push');

        unlink(storage_path('app/COMMIT'));
    }
});
