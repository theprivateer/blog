<?php

use App\Http\Controllers\PageController;
use App\Models\User;
use App\Services\SitemapService;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Http\Controllers\CategoryController;
use Privateer\Basecms\Http\Controllers\PostController;
use Privateer\Basecms\Models\Asset;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Visit;

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
        'user' => User::class,
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
    | This project keeps flat-file backups enabled by default so content
    | continues to sync into the /content directory. Override the environment
    | variable below if you need to disable markdown backups and sitemap sync.
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
    | This project can optionally record anonymous website visits for the
    | dashboard analytics widgets. Enable the environment variable below to
    | store visit data while still skipping authenticated and Livewire traffic.
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
    | This project enables the page builder by default so editors can choose
    | between markdown and Builder-based page content on a per-page basis.
    | Override the environment variable below to disable the builder UI.
    |
    */

    'pages' => [
        'builder' => [
            'enabled' => env('BASECMS_PAGE_BUILDER_ENABLED', true),
            'blocks' => [
                MarkdownBlock::class,
                HeaderBlock::class,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Controllers
    |--------------------------------------------------------------------------
    |
    | These controller classes are used for the package-managed public CMS
    | routes. This project overrides the homepage controller so site-specific
    | data can be composed in the app while the package keeps the route shape.
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
        'resources_path' => app_path('Filament/Resources/Notes'),
        'resources_namespace' => 'App\\Filament\\Resources\\Notes',
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
