<?php

namespace Privateer\Basecms\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Privateer\Basecms\Console\Commands\GenerateSitemap;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Listeners\FlatFileBackupListener;

class BasecmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/basecms.php', 'basecms');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        Event::listen(PostSaved::class, [FlatFileBackupListener::class, 'handle']);
        Event::listen(PostDeleted::class, [FlatFileBackupListener::class, 'handle']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSitemap::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../../config/basecms.php' => config_path('basecms.php'),
        ], 'basecms-config');
    }
}
