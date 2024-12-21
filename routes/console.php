<?php

use Illuminate\Support\Str;
use Spatie\Sitemap\Sitemap;
use Spatie\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Spatie\Sitemap\Tags\Url;

Artisan::command('sitemap', function () {
    $sitemap = Sitemap::create();

    $posts = Sheets::collection('posts')
                ->all()
                ->sortByDesc('date');

    $sitemap->add(Url::create('/')
        ->setLastModificationDate($posts->first()->date)
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

    foreach ($posts as $post) {
        $sitemap->add(Url::create(route('posts.show', $post->slug))
                ->setLastModificationDate($post->date));
    }

    $slashes = Sheets::collection('slashes')
                    ->all()
                    ->filter(function ($slash) {
                        return ! $slash->draft;
                    });

    foreach ($slashes as $slash) {
        $sitemap->add(Url::create(route('slashes.show', $slash->slug))
                    ->setLastModificationDate(Carbon\Carbon::parse($post->modified))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
    }

    $sitemap->writeToFile(public_path('sitemap.xml'));
});

Artisan::command('commit', function () {
    if (file_exists(storage_path('app/COMMIT'))) {
        $contents = file_get_contents(storage_path('app/COMMIT'));

        info('Running commit: ' . $contents);

        $commit_message = Str::of($contents)->limit(47);

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
