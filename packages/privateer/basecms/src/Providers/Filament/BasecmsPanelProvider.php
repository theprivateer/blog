<?php

namespace Privateer\Basecms\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class BasecmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id((string) config('basecms.panel.id', 'admin'))
            ->path((string) config('basecms.panel.path', 'admin'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: __DIR__.'/../../Filament/Resources', for: 'Privateer\Basecms\Filament\Resources')
            ->discoverWidgets(in: __DIR__.'/../../Filament/Widgets', for: 'Privateer\Basecms\Filament\Widgets')
            ->pages([
                Dashboard::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if ($path = config('basecms.filament.resources_path')) {
            $panel->discoverResources(in: $path, for: (string) config('basecms.filament.resources_namespace'));
        }

        if ($path = config('basecms.filament.pages_path')) {
            $panel->discoverPages(in: $path, for: (string) config('basecms.filament.pages_namespace'));
        }

        if ($path = config('basecms.filament.widgets_path')) {
            $panel->discoverWidgets(in: $path, for: (string) config('basecms.filament.widgets_namespace'));
        }

        return $panel;
    }
}
