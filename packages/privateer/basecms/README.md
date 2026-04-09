# Privateer Base CMS

Reusable Laravel CMS package for posts, pages, categories, sites, domains, assets, visits, public CMS routes, and a Filament admin panel.

This package is currently consumed locally from the host application using a Composer path repository, but it is structured to be split into its own repository later.

## What It Owns

- Sites and domains for multi-site setups
- Posts, pages, categories, metadata, assets, and visits
- Public controllers and route registration for:
  - `/`
  - `/blog`
  - `/blog/{post}`
  - `/category/{category}`
  - `/{page}`
- Filament panel provider and CMS resources for posts, pages, and categories
- Filament tenancy integration for site switching
- Visit tracking middleware, visitor classification, and analytics widgets
- Optional flat-file backup listener and Markdown editor asset tracking
- Optional AI-generated meta descriptions for Post and Page edit screens
- Package migrations and factories

## What The Host App Still Owns

- Blade templates and overall site presentation
- Notes and any other custom content types
- Route composition order in `routes/web.php`
- App-specific sitemap composition
- App-specific Filament resources, pages, and widgets discovered into the package-owned panel
- Site access policy on the authenticated user model when Filament tenancy is enabled

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
php artisan basecms:install
```

`basecms:install` walks through the first-time setup with Laravel Prompts. It will:

- scaffold `app/Models/User.php` only when the host app does not already have one
- create the first admin user
- create the first site
- suggest the site key from the entered site name while still allowing you to override it

Existing applications with a custom auth model are supported. The install command reuses an existing `app/Models/User.php` and does not overwrite it.

## Commands

Base CMS ships with:

- `php artisan basecms:create-site`
- `php artisan basecms:install`
- `php artisan basecms:generate-static`
- `php artisan basecms:generate-sitemap`
- `php artisan basecms:generate-meta-descriptions {post|page} [--force]`
- `php artisan basecms:reclassify-visits`
- `php artisan basecms:make-block Hero`

`basecms:make-block` scaffolds:

- `app/Filament/Blocks/HeroBlock.php`
- `resources/views/blocks/page-builder/hero.blade.php`

It also appends `\App\Filament\Blocks\HeroBlock::class` to `config/basecms.php` so the new block is immediately available in `basecms.pages.builder.blocks`.

`basecms:generate-static` renders the configured public site into a static output directory using the live Laravel app, public routes, Blade views, and model-backed content.

## Service Providers

The package auto-registers:

- `Privateer\Basecms\Providers\BasecmsServiceProvider`
- `Privateer\Basecms\Providers\Filament\BasecmsPanelProvider`

No manual provider registration is required when Laravel package discovery is enabled.

## Configuration

Publish or create a host-side `config/basecms.php` and configure:

- model class mappings
- multi-site toggle and current-site resolver
- sitemap service
- static site generation
- markdown editor attachment disk
- AI-assisted editorial helpers
- flat-file backup toggle
- visit-tracking toggle
- page-builder feature flag and block classes
- public Blade view names
- app Filament discovery paths and namespaces
- panel id and path

Example:

```php
use App\Models\User;
use App\Models\Visit;
use App\Services\SitemapService;
use App\StaticSite\NoteStaticRouteExporter;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Http\Controllers\CategoryController;
use Privateer\Basecms\Http\Controllers\PageController;
use Privateer\Basecms\Http\Controllers\PostController;
use Privateer\Basecms\Models\Asset;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Metadata;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\DomainCurrentSiteResolver;

return [
    'models' => [
        'post' => Post::class,
        'page' => Page::class,
        'category' => Category::class,
        'metadata' => Metadata::class,
        'asset' => Asset::class,
        'visit' => Visit::class,
        'site' => Site::class,
        'domain' => Domain::class,
        'user' => User::class,
    ],
    'multisite' => [
        'enabled' => env('BASECMS_MULTISITE_ENABLED', false),
        'resolver' => DomainCurrentSiteResolver::class,
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
            NoteStaticRouteExporter::class,
        ],
    ],
    'markdown_editor' => [
        'attachments_disk' => 's3',
    ],
    'ai' => [
        'generate_meta_descriptions' => [
            'enabled' => env('BASECMS_GENERATE_META_DESCRIPTIONS_ENABLED', false),
        ],
    ],
    'flat_file_backup' => [
        'enabled' => env('BASECMS_FLAT_FILE_BACKUP_ENABLED', false),
    ],
    'visits' => [
        'track_visits' => env('BASECMS_TRACK_VISITS', false),
    ],
    'pages' => [
        'builder' => [
            'enabled' => env('BASECMS_PAGE_BUILDER_ENABLED', false),
            'blocks' => [
                MarkdownBlock::class,
                HeaderBlock::class,
            ],
        ],
    ],
    'controllers' => [
        'page' => PageController::class,
        'post' => PostController::class,
        'category' => CategoryController::class,
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
        'resources_path' => app_path('Filament/Resources'),
        'resources_namespace' => 'App\\Filament\\Resources',
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

## Multi-Site

Base CMS ships with first-class multi-site support built around two package models:

- `Privateer\Basecms\Models\Site`
- `Privateer\Basecms\Models\Domain`

Each site has:

- a human-readable `name`
- a stable `key` used for flat-file backups and internal identification
- one or more domains, with an optional primary domain for canonical URL generation

Package-owned content belongs to a site:

- posts
- pages
- categories
- assets
- visits

Host-app content types such as Notes can join the same model by adding `site_id` and a `site()` relationship.

### Resolution Rules

- Multi-site enabled: resolve the site from the incoming host via `domains.domain`
- Unknown host in multi-site mode: return `404`
- Multi-site disabled: use the first `sites` row as the active site

### Creating Additional Sites

When multi-site mode is enabled, you can add another site with:

```bash
php artisan basecms:create-site
```

This command:

- aborts immediately when `basecms.multisite.enabled` is `false`
- prompts for the site name and site key
- suggests the site key from the entered site name
- creates only the site record and does not create a new user

### Managing Domains In Filament

When multi-site mode is enabled, the Filament admin panel also exposes a `Domains` resource for the active tenant site.

- domains are managed on a single list page with modal create and edit actions
- the resource is hidden and blocked entirely when `basecms.multisite.enabled` is `false`
- each site keeps exactly one primary domain whenever it has one or more domains
- marking a domain as primary clears the flag from the site's other domains
- deleting the primary domain automatically promotes another remaining domain for that site

### Slugs

Posts, pages, categories, and notes can reuse the same slug on different sites. Base CMS scopes slug generation and public route lookup by `site_id`.

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

When multi-site is enabled, post, page, category, and note lookups are resolved within the current site only.

## Filament

The package owns the Filament panel and discovers:

- package resources for posts, pages, and categories
- package widgets for visit analytics
- app-specific resources/pages/widgets from the configured discovery paths

### Multi-Tenant Admin

Base CMS supports Filament multi-tenancy with `Site` as the tenant model.

- when `basecms.multisite.enabled` is `true`, the panel enables site tenancy
- package resources and app-discovered resources such as Notes can be tenant-scoped
- CRUD queries, relationship selectors, analytics, and markdown-editor assets can all be scoped to the active tenant
- host apps should implement Filament’s tenant interfaces on the authenticatable `User` model

The current host app chooses the simplest v1 access model: every admin can access every site.

Markdown editor uploads use the filesystem disk configured in `basecms.markdown_editor.attachments_disk`. The package default is `local`, but host applications can point uploads at `s3` or any other configured Laravel filesystem disk.

## AI Meta Description Generator

Base CMS can optionally expose a manual AI-powered meta description action on the Post and Page edit screens.

- The feature is disabled by default.
- Enable it with `basecms.ai.generate_meta_descriptions.enabled` or `BASECMS_GENERATE_META_DESCRIPTIONS_ENABLED=true`.
- When enabled, editors can trigger a manual action that uses the current edit form title plus rendered body content to fill `metadata.description`.
- The generated value is plain text, intended for search snippets, and excludes the page or post title.
- The action updates the form state only; editors still save the record normally to persist the generated description.
- Base CMS also includes a bulk command: `php artisan basecms:generate-meta-descriptions {post|page}`.
- By default, the command only processes records with missing or blank `metadata.description` values.
- Pass `--force` to regenerate descriptions for all matching records, including ones that already have a description.
- The command respects `basecms.ai.generate_meta_descriptions.enabled`.

This feature requires the host application to install and configure the Laravel AI SDK with a working text-generation provider.

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

When multi-site is enabled, static export runs for one site at a time. Base CMS uses the active site context plus the site’s primary domain when generating URLs and sitemap output.

## Page Builder Blocks

The page builder block list is configured in `basecms.pages.builder.blocks` as an array of class strings.

Each configured block class should implement:

```php
\Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlock
```

The package ships two default blocks:

- `Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock`
- `Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock`

To scaffold a new app block that follows the same conventions, run:

```bash
php artisan basecms:make-block Hero
```

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

- is written after the response is sent via terminable middleware
- is controlled by `basecms.visits.track_visits`
- skips authenticated users
- skips `livewire-*` requests
- classifies each visit at record time via `VisitClassifier`
- records visits against the active site

## Visit Retention And Pruning

Visit retention is best handled in the host app, not in the package model itself. The recommended setup is to extend `Privateer\Basecms\Models\Visit` in `App\Models\Visit`, add Laravel's `Prunable` trait there, update `basecms.models.visit` to use the app model, and schedule `model:prune` from the app.

## Flat-File Backups

Flat-file backups are optional. The package default is disabled, and host apps can enable the feature through:

```php
'flat_file_backup' => [
    'enabled' => env('BASECMS_FLAT_FILE_BACKUP_ENABLED', false),
],
```

When enabled, the package ships the shared backup listener and backup service. The host app remains responsible for:

- filesystem disk configuration
- app-level sitemap composition

The package listener calls the configured sitemap service from `basecms.services.sitemap` after save events.

### Backup Layout

Base CMS expects a portable site-first content structure:

```text
content/
  default/
    posts/
    pages/
    categories/
    notes/
```

This keeps all content for a site together and makes the backup tree easier to move between projects.

Package-managed records write to:

- `content/{site}/posts/...`
- `content/{site}/pages/...`
- `content/{site}/categories/...`

Host-app content types such as Notes can follow the same pattern:

- `content/{site}/notes/...`

### Reseeding

The package does not ship the host app’s reseed command, but the intended flow is:

- scan `content/*/{type}` for each content type
- infer the site from the first path segment
- create or look up that site by key
- rebuild the database with `site_id` set from the folder path

## Markdown Editor Uploads

The package includes Markdown editor upload handling for package-owned content:

- files are stored on the configured attachment disk
- an `assets` row is created immediately
- the created asset is assigned to the active site
- uploads on existing records can be attached immediately
- uploads on create forms can remain unlinked until the host app performs later reconciliation

## Migrations

The package loads migrations automatically from `database/migrations`.

These cover:

- sites
- domains
- posts
- pages
- metadata
- visits
- categories
- post/category relationship
- assets

## Factories

The package includes factories for:

- `Site`
- `Domain`
- `Post`
- `Page`
- `Category`
- `Metadata`
- `Visit`
- `Asset`

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
- a site access policy on the `User` model if Filament tenancy is enabled
- a sitemap service if the app wants sitemap regeneration on content save
- feed configuration and any site-level composition outside the shared CMS domain
