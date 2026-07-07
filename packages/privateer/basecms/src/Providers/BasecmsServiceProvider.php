<?php

namespace Privateer\Basecms\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Facades\Mcp;
use Privateer\Basecms\Console\Commands\CreateSite;
use Privateer\Basecms\Console\Commands\GenerateMetaDescriptions;
use Privateer\Basecms\Console\Commands\GenerateSitemap;
use Privateer\Basecms\Console\Commands\GenerateStaticSite;
use Privateer\Basecms\Console\Commands\Install;
use Privateer\Basecms\Console\Commands\MakeBlock;
use Privateer\Basecms\Console\Commands\ManageMcpToken;
use Privateer\Basecms\Console\Commands\ReclassifyVisits;
use Privateer\Basecms\Contracts\ResolvesCurrentSite;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Http\Middleware\AuthenticateMcpRequest;
use Privateer\Basecms\Listeners\FlatFileBackupListener;
use Privateer\Basecms\Mcp\BasecmsMcpServer;
use Privateer\Basecms\Mcp\Support\ContentTypeRegistry;
use Privateer\Basecms\Services\SiteManager;

class BasecmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/basecms.php', 'basecms');

        $this->app->bind(ResolvesCurrentSite::class, function (): ResolvesCurrentSite {
            $resolver = (string) config('basecms.multisite.resolver');

            return $this->app->make($resolver);
        });

        $this->app->singleton(SiteManager::class);
        $this->app->singleton(ContentTypeRegistry::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'basecms');

        Event::listen(PostSaved::class, [FlatFileBackupListener::class, 'handle']);
        Event::listen(PostDeleted::class, [FlatFileBackupListener::class, 'handle']);

        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSite::class,
                GenerateMetaDescriptions::class,
                GenerateStaticSite::class,
                GenerateSitemap::class,
                Install::class,
                ManageMcpToken::class,
                MakeBlock::class,
                ReclassifyVisits::class,
            ]);
        }

        $this->bootMcp();

        $this->publishes([
            __DIR__.'/../../config/basecms.php' => config_path('basecms.php'),
        ], 'basecms-config');
    }

    protected function bootMcp(): void
    {
        if (! config('basecms.mcp.enabled')) {
            return;
        }

        Mcp::local((string) config('basecms.mcp.local_handle', 'basecms'), BasecmsMcpServer::class);

        Mcp::web((string) config('basecms.mcp.web_route', '/mcp'), BasecmsMcpServer::class)
            ->middleware([AuthenticateMcpRequest::class]);

        if (config('basecms.mcp.oauth.enabled')) {
            Mcp::oauthRoutes();
        }
    }
}
