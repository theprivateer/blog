# Privateer Base CMS

Reusable Laravel CMS package for posts, pages, categories, assets, visits, public CMS routes, and a Filament admin panel.

This package is currently consumed locally from the host application using a Composer path repository, but it is structured to be split into its own repository later.

## What It Owns

- Posts, pages, categories, metadata, assets, and visits
- Public controllers and routes for:
  - `/`
  - `/blog`
  - `/blog/{post}`
  - `/category/{category}`
  - `/posts` and `/posts/{post}` legacy redirects
  - `/{page}` wildcard page route
- Filament panel provider and CMS resources for posts, pages, and categories
- Visit tracking middleware and analytics widgets
- Optional flat-file backup listener and Markdown editor asset tracking
- Package migrations and factories

## What The Host App Still Owns

- Blade templates and overall site presentation
- Notes and any other custom content types
- Route composition order in `routes/web.php`
- App-specific sitemap composition
- App-specific Filament resources, pages, and widgets discovered into the package-owned panel

## Installation

Add the package as a path repository in the host app `composer.json`:

```json
"repositories": [
  {
    "type": "path",
    "url": "packages/privateer/basecms",
    "options": {
      "symlink": true
    }
  }
]
```

Require the package:

```json
"require": {
  "privateer/basecms": "@dev"
}
```

Then install dependencies:

```bash
composer update privateer/basecms --no-interaction
php artisan migrate
```

## Commands

Base CMS ships with:

- `php artisan basecms:generate-static`
- `php artisan basecms:generate-sitemap`
- `php artisan basecms:make-block Hero`

`basecms:make-block` scaffolds:

- `app/Filament/Blocks/HeroBlock.php`
- `resources/views/blocks/page-builder/hero.blade.php`

It also appends `\App\Filament\Blocks\HeroBlock::class` to `config/basecms.php` so the new block is immediately available in `basecms.pages.builder.blocks`.

`basecms:generate-static` renders the configured public site into a static output directory using the live Laravel app, public routes, Blade views, and model-backed content. The command shows a progress bar during export so larger sites do not appear idle.

## Service Providers

The package auto-registers:

- `Privateer\Basecms\Providers\BasecmsServiceProvider`
- `Privateer\Basecms\Providers\Filament\BasecmsPanelProvider`

No manual provider registration is required when Laravel package discovery is enabled.

## Configuration

Publish or create a host-side `config/basecms.php` and configure:

- model class mappings
- sitemap service
- static site generation
- markdown editor attachment disk
- flat-file backup toggle
- visit-tracking toggle
- page-builder feature flag and block classes
- public Blade view names
- app Filament discovery paths and namespaces
- panel id and path

Example:

```php
use App\Models\Asset;
use App\Models\Category;
use App\Models\Metadata;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Models\Visit;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use App\Services\SitemapService;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;

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
    'static_site' => [
        'enabled' => env('BASECMS_STATIC_SITE_ENABLED', false),
        'output_path' => storage_path('app/static-site'),
        'base_url' => env('BASECMS_STATIC_SITE_BASE_URL', env('APP_URL', 'http://localhost')),
        'clean_output_before_build' => true,
        'generate_sitemap' => true,
        'generate_feeds' => true,
        'runtime_overrides' => [
            'app.env' => 'production',
            'app.debug' => false,
            'basecms.visits.track_visits' => false,
            'boost.browser_logs_watcher' => false,
        ],
        'exporters' => [
            \App\StaticSite\NoteStaticRouteExporter::class,
        ],
    ],
    'markdown_editor' => [
        'attachments_disk' => 's3',
    ],
    'flat_file_backup' => [
        'enabled' => env('BASECMS_FLAT_FILE_BACKUP_ENABLED', true),
    ],
    'visits' => [
        'track_visits' => env('BASECMS_TRACK_VISITS', false),
    ],
    'pages' => [
        'builder' => [
            'enabled' => env('BASECMS_PAGE_BUILDER_ENABLED', true),
            'blocks' => [
                MarkdownBlock::class,
                HeaderBlock::class,
            ],
        ],
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
```

## Static Site Generation

Run the exporter with:

```bash
php artisan basecms:generate-static
```

Base CMS resolves a route manifest, renders each route through Laravel's real HTTP pipeline, and writes the result to the configured static output directory. By default, files are written to `storage/app/static-site`.

The package exports its own CMS routes:

- `/`
- `/blog` and paginated blog indexes
- `/blog/{post}`
- `/category/{category}` and paginated category indexes
- `/{page}` for non-draft pages
- `/posts` and `/posts/{post}` as static redirect artifacts

Host applications can extend the export set by registering classes in `basecms.static_site.exporters`. This is how app-owned routes like Notes are added without forking the package.

The static export also:

- optionally copies feed endpoints into the output when `generate_feeds` is enabled
- optionally generates and copies the sitemap when `generate_sitemap` is enabled
- copies public local assets from `public/`, excluding runtime server files such as `index.php` and `.htaccess`
- rewrites internal links so exported pagination and route URLs point at static-friendly paths

During export, Base CMS applies a scoped runtime profile from `basecms.static_site.runtime_overrides`. The defaults force production-like rendering for the build by setting `app.env` to `production`, `app.debug` to `false`, and disabling visit tracking. If Laravel Boost is installed in the host app, the same runtime profile disables the browser logs watcher so `<script id="browser-logger-active">` is not injected into static HTML. If Boost is not installed, the export continues normally.

### Static Exporters

Additional exporters should implement:

```php
\Privateer\Basecms\StaticSite\StaticRouteExporter
```

and return `\Privateer\Basecms\StaticSite\StaticRoute` objects describing the source URI, public URI, output path, and response type to export.

This keeps the package responsible for the export pipeline while allowing host apps to register custom route manifests for content types such as Notes.

## Routes

The package intentionally does not auto-register public web routes. The host app should compose them in `routes/web.php` so route order remains explicit.

Recommended host routing:

```php
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use Privateer\Basecms\Routes\BasecmsRoutes;

Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
Route::get('/notes/{note}', [NoteController::class, 'show'])->name('notes.show');

Route::feeds();

BasecmsRoutes::register();
```

Register app-specific routes like Notes before `BasecmsRoutes::register()` so the package wildcard page route stays last.

## Views

The package does not ship frontend Blade templates for the public site. Controllers render host-app views configured through `basecms.views`.

Expected default views:

- `pages.index`
- `pages.show`
- `posts.index`
- `posts.show`
- `categories.show`

## Filament

The package owns the Filament panel and discovers:

- package resources for posts, pages, and categories
- package widgets for visit analytics
- app-specific resources/pages/widgets from the configured discovery paths

This allows the host app to keep custom admin code, such as Notes, in the app while using the package panel.

Markdown editor uploads use the filesystem disk configured in `basecms.markdown_editor.attachments_disk`. The package default is `local`, but host applications can point uploads at `s3` or any other configured Laravel filesystem disk.

## Page Builder Blocks

The page builder block list is configured in `basecms.pages.builder.blocks` as an array of class strings.

Each configured block class should implement:

```php
\Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlock
```

The interface currently requires:

```php
public function schema(): array;
```

Base CMS resolves each configured class through the container and converts it into a Filament builder block.

For this first pass:

- the block name is derived from the class basename in kebab-case
- the block label is derived from the class basename in title case
- a trailing `Block` suffix is removed before deriving the name and label

The package ships one default builder block:

- `Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock`
- `Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock`

The `HeaderBlock` is intended for the top of a page and provides:

- a `heading` text field
- a `content` markdown editor for longer-form supporting copy

The built-in markdown editors used by both package resources and page-builder blocks also respect `basecms.markdown_editor.attachments_disk`.

Example host-app config:

```php
'pages' => [
    'builder' => [
        'enabled' => env('BASECMS_PAGE_BUILDER_ENABLED', true),
        'blocks' => [
            \Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock::class,
            \Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock::class,
            \App\Filament\Blocks\PageBuilder\HeroBlock::class,
        ],
    ],
],
```

Frontend block rendering remains out of scope for now. Builder-backed pages are still admin-focused until rendering hooks are added in a later pass.

To scaffold a new app block that follows the same conventions, run:

```bash
php artisan basecms:make-block Hero
```

This generates an `App\Filament\Blocks\HeroBlock` class with a matching `blocks.page-builder.hero` view and registers the block in `config/basecms.php`.

## Middleware

The host app should reference the package middleware in `bootstrap/app.php`:

```php
use Privateer\Basecms\Http\Middleware\TrackWebsiteVisits;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        TrackWebsiteVisits::class,
    ]);
})
```

Visit tracking:

- is controlled by `basecms.visits.track_visits`
- skips authenticated users
- skips `livewire-*` requests

## Flat-File Backups

Flat-file backups are optional. The package default is disabled, and host apps can enable the feature through:

```php
'flat_file_backup' => [
    'enabled' => env('BASECMS_FLAT_FILE_BACKUP_ENABLED', false),
],
```

When enabled, the package ships the shared backup listener and backup service. The host app remains responsible for:

- filesystem disk configuration
- the actual `/content` directory structure
- app-level sitemap composition

The package listener calls the configured sitemap service from `basecms.services.sitemap` after save events.

This project enables flat-file backups by default in its published config with `BASECMS_FLAT_FILE_BACKUP_ENABLED=true`.

## Markdown Editor Uploads

The package includes Markdown editor upload handling for package-owned content:

- files are stored on the configured attachment disk
- an `assets` row is created immediately
- uploads on existing records can be attached immediately
- uploads on create forms can remain unlinked until the host app performs later reconciliation

## Models And Relationships

The package models default to package class names, but relation targets are configurable through `basecms.models`. In the host app, you should point these at the app wrapper models so:

- existing tests can keep using app namespaces
- morph types and relation instances stay app-facing
- the package can still operate as a reusable core

## Migrations

The package loads migrations automatically from:

- `database/migrations`

These cover:

- posts
- pages
- metadata
- visits
- categories
- post/category relationship
- assets

## Factories

The package includes factories for:

- `Post`
- `Page`
- `Category`
- `Metadata`
- `Visit`
- `Asset`

The host app may keep using its own app-facing model wrappers with these tables.

## Local Development

Useful commands from the host app root:

```bash
composer update privateer/basecms --no-interaction
php artisan migrate
php artisan test --compact
vendor/bin/pint --dirty --format agent
```

## Current Host-App Expectations

This package assumes the host app provides:

- an authenticatable `User` model if asset ownership is needed
- public Blade templates for posts, pages, and categories
- any custom content types such as Notes
- a sitemap service if the app wants sitemap regeneration on content save
- feed configuration and any site-level composition outside the shared CMS domain
