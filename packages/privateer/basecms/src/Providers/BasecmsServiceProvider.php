<?php

namespace Privateer\Basecms\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Privateer\Basecms\Console\Commands\GenerateMetaDescriptions;
use Privateer\Basecms\Console\Commands\GenerateSitemap;
use Privateer\Basecms\Console\Commands\GenerateStaticSite;
use Privateer\Basecms\Console\Commands\MakeBlock;
use Privateer\Basecms\Console\Commands\ReclassifyVisits;
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
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'basecms');

        Event::listen(PostSaved::class, [FlatFileBackupListener::class, 'handle']);
        Event::listen(PostDeleted::class, [FlatFileBackupListener::class, 'handle']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateMetaDescriptions::class,
                GenerateStaticSite::class,
                GenerateSitemap::class,
                MakeBlock::class,
                ReclassifyVisits::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../../config/basecms.php' => config_path('basecms.php'),
        ], 'basecms-config');
    }
}
