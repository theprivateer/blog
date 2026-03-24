<?php

use App\Models\Asset;
use App\Models\Category;
use App\Models\Metadata;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Models\Visit;
use App\Services\SitemapService;

return [
    'models' => [
        'post' => Post::class,
        'page' => Page::class,
        'category' => Category::class,
        'metadata' => Metadata::class,
        'asset' => Asset::class,
        'visit' => Visit::class,
        'user' => User::class,
    ],
    'services' => [
        'sitemap' => SitemapService::class,
    ],
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
    'filament' => [
        'resources_path' => app_path('Filament/Resources/Notes'),
        'resources_namespace' => 'App\\Filament\\Resources\\Notes',
        'pages_path' => null,
        'pages_namespace' => null,
        'widgets_path' => null,
        'widgets_namespace' => null,
    ],
    'panel' => [
        'id' => 'admin',
        'path' => 'admin',
    ],
];
