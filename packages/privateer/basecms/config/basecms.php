<?php

use Privateer\Basecms\Http\Controllers\CategoryController;
use Privateer\Basecms\Http\Controllers\PageController;
use Privateer\Basecms\Http\Controllers\PostController;
use Privateer\Basecms\Models\Asset;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\SitemapService;

return [

    /*
    |--------------------------------------------------------------------------
    | Model Class Bindings
    |--------------------------------------------------------------------------
    |
    | These model classes are used by Base CMS for its shared content types
    | and related features. You may override them in the host application
    | when you need to swap in custom implementations.
    |
    */

    'models' => [
        'post' => Post::class,
        'page' => Page::class,
        'category' => Category::class,
        'metadata' => Metadata::class,
        'asset' => Asset::class,
        'visit' => Visit::class,
        'user' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Class Bindings
    |--------------------------------------------------------------------------
    |
    | These optional service bindings allow the host application to plug in
    | app-specific orchestration, such as sitemap generation that includes
    | content types outside of the shared Base CMS package.
    |
    */

    'services' => [
        'sitemap' => SitemapService::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Flat-File Backups
    |--------------------------------------------------------------------------
    |
    | Base CMS can optionally mirror content changes to markdown files using
    | the shared backup listener and service. When disabled, save and delete
    | events will not write files or trigger sitemap regeneration.
    |
    */

    'flat_file_backup' => [
        'enabled' => env('BASECMS_FLAT_FILE_BACKUP_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Visit Tracking
    |--------------------------------------------------------------------------
    |
    | Base CMS can optionally record anonymous website visits for analytics.
    | When enabled, the tracking middleware stores request snapshots while
    | skipping authenticated users and internal Livewire requests.
    |
    */

    'visits' => [
        'track_visits' => env('BASECMS_TRACK_VISITS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Builder
    |--------------------------------------------------------------------------
    |
    | Base CMS can optionally expose a per-page builder mode in the admin UI.
    | When enabled, editors may switch between the traditional markdown body
    | field and a Builder-based block editor for individual pages.
    |
    */

    'pages' => [
        'builder' => [
            'enabled' => env('BASECMS_PAGE_BUILDER_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Controllers
    |--------------------------------------------------------------------------
    |
    | These controller classes are used for the package-managed public CMS
    | routes. Host applications may override them to customize website
    | behavior while keeping the package routes and route names unchanged.
    |
    */

    'controllers' => [
        'page' => PageController::class,
        'post' => PostController::class,
        'category' => CategoryController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend View Names
    |--------------------------------------------------------------------------
    |
    | These view names are used by the package controllers when rendering
    | the public website. They default to conventional Blade view names so
    | the host application remains the owner of presentation and theming.
    |
    */

    'views' => [
        'pages' => [
            'index' => 'pages.index',
            'show' => 'pages.show',
        ],
        'posts' => [
            'index' => 'posts.index',
            'show' => 'posts.show',
        ],
        'categories' => [
            'show' => 'categories.show',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | App Filament Discovery
    |--------------------------------------------------------------------------
    |
    | The package owns the admin panel, but it can also discover additional
    | Filament resources, pages, and widgets from the host application. Set
    | these values when you want app-specific admin features in the same panel.
    |
    */

    'filament' => [
        'resources_path' => null,
        'resources_namespace' => null,
        'pages_path' => null,
        'pages_namespace' => null,
        'widgets_path' => null,
        'widgets_namespace' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Panel Configuration
    |--------------------------------------------------------------------------
    |
    | These values control the shared admin panel that is registered by the
    | package. Adjust them if the host application needs a custom panel ID
    | or admin path.
    |
    */

    'panel' => [
        'id' => 'admin',
        'path' => 'admin',
    ],
];
