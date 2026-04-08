# Phil Stephens

Personal blog and portfolio site built with Laravel 13.

Shared CMS functionality now lives in the local package [packages/privateer/basecms/README.md](/Users/phil/Herd/philstephens/packages/privateer/basecms/README.md). This app keeps the site presentation layer, Notes, and app-specific composition around the package.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.4, Symfony 8
- **Admin**: Filament v5 (Livewire v4, Alpine.js)
- **Frontend**: Blade templates, KelpUI, Tailwind CSS v4, Vite 7
- **Content**: Database-backed content with optional markdown flat-file backups
- **Feeds**: RSS, Atom, JSON via spatie/laravel-feed
- **SEO**: Auto-generated sitemap via spatie/laravel-sitemap
- **Storage**: SQLite (local), AWS S3 (images)
- **Testing**: PHPUnit 12

## Content Types

| Type | Description |
|------|-------------|
| **Posts** | Blog articles with categories, intros, and publish dates |
| **Notes** | Short-form content with optional external links |
| **Pages** | Static pages (About, Now, Uses, Work, etc.) with optional custom templates |
| **Categories** | Organisational tags for posts |

Posts, pages, and categories are provided by the `privateer/basecms` local package. Notes remain app-specific. Flat-file backups are optional at the package level and enabled by default in this project via `BASECMS_FLAT_FILE_BACKUP_ENABLED=true`, so content continues to sync to `/content` with YAML frontmatter.

## Local Development

Served by [Laravel Herd](https://herd.laravel.com) at `https://philstephens.test`.

```bash
composer install
npm install
npm run dev          # or npm run build for production assets
php artisan migrate --seed
```

If the local package has changed, refresh it with:

```bash
composer update privateer/basecms --no-interaction
```

### Useful Commands

```bash
php artisan basecms:generate-sitemap   # Regenerate XML sitemap
php artisan basecms:reclassify-visits  # Re-classify all stored visits (run after classifier rule changes)
php artisan basecms:make-block Hero    # Scaffold a custom page-builder block
php artisan app:re-seed-content    # Re-seed database from /content markdown files
php artisan test --compact         # Run test suite
vendor/bin/pint --dirty            # Format changed PHP files
```

## Visit Retention

Visit pruning is intentionally app-owned rather than package-owned. The recommended approach is to wrap the package model in `App\Models\Visit`, add Laravel's `Prunable` trait there, point `basecms.models.visit` at the app model, and schedule `model:prune` daily.

This project currently prunes visits older than 30 days:

```php
// app/Models/Visit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;

class Visit extends \Privateer\Basecms\Models\Visit
{
    use Prunable;

    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<', now()->subDays(30));
    }
}
```

```php
// config/basecms.php
'models' => [
    'visit' => \App\Models\Visit::class,
],
```

```php
// bootstrap/app.php
->withSchedule(function (Schedule $schedule): void {
    $schedule->command('model:prune', [
        '--model' => [\App\Models\Visit::class],
    ])->daily();
})
```

Keeping retention in the app means the package can stay unopinionated, while each host app can choose its own retention window or pruning policy.

## Admin Panel

Filament admin at `/admin` is owned by the Base CMS package. The package registers the shared CMS resources for posts, pages, and categories, then discovers app-specific Filament code for Notes. Markdown editor uploads use the filesystem disk configured in `basecms.markdown_editor.attachments_disk`; this project sets that to `s3` and tracks uploaded files via the `Asset` model. The dashboard includes visit analytics widgets showing traffic stats over a configurable time window, plus a visitor classification breakdown separating human traffic from AI crawlers, search crawlers, and other bots.

When `basecms.multisite.enabled` is enabled, the panel uses Filament tenancy with `Site` as the tenant model. Editors can switch sites from the tenant switcher and all CMS CRUD screens are scoped to the selected site automatically. In the current host app, all admin users can access all sites.

Base CMS also includes an optional AI-assisted meta description generator for Posts and Pages. When `basecms.ai.generate_meta_descriptions.enabled` is enabled, editors get a manual action on the edit screen that uses the Laravel AI SDK plus the current form title and rendered body content to fill `metadata.description` without auto-saving. The host app must have the Laravel AI SDK installed and at least one working text provider/API key configured.

## Architecture

- **Package split**: Shared CMS code lives in `packages/privateer/basecms`; the app keeps Notes, Blade templates, feed composition, and route composition
- **Configurable controllers**: Package controllers (Page, Post, Category) are swappable via `basecms.controllers` config, so host apps can override routing behaviour
- **Multi-site support**: Package-owned `Site` and `Domain` models allow content to be scoped per website. When multi-site is enabled, the current site resolves from the request host; when disabled, the first `sites` record is treated as the active site.
- **Site-scoped slugs**: Posts, pages, categories, and notes can reuse the same slug on different sites. Public route resolution is site-aware when multi-site is enabled.
- **Flat-file backup**: Optional package feature controlled by `basecms.flat_file_backup.enabled`; this project enables it by default so shared CMS content syncs to `content/{site}/{type}/` markdown files
- **Polymorphic metadata**: SEO title/description stored via `Metadata` on Posts, Pages, and Categories
- **Visit tracking**: Optional analytics (enable via `BASECMS_TRACK_VISITS=true` in `.env`), skips authenticated users and `livewire-*` requests; classifies each visit as human, AI crawler, search crawler, other bot, or unknown at record time via `VisitClassifier`
- **Site-owned analytics and assets**: Visits and markdown-editor uploads are recorded against the active site so dashboards and uploads stay tenant-aware
- **Slug generation**: Automatic via spatie/laravel-sluggable
- **Markdown rendering**: spatie/laravel-markdown with Shiki syntax highlighting (`github-dark` theme), auto-anchored headings, and GitHub-flavored markdown extensions
- **Asset tracking**: File uploads from markdown editors use the disk configured in `basecms.markdown_editor.attachments_disk`; this project points that at S3 and tracks uploads via the polymorphic `Asset` model
- **AI metadata helper**: Optional AI-generated SEO descriptions for Posts and Pages (enable via `basecms.ai.generate_meta_descriptions.enabled` or `BASECMS_GENERATE_META_DESCRIPTIONS_ENABLED=true`). The admin action is manual, uses the current edit form state, and fills `metadata.description` without saving automatically. Requires the Laravel AI SDK and a configured text provider.
- **Page builder**: Optional block-based page editing (enable via `BASECMS_PAGE_BUILDER_ENABLED=true`). Ships with `Markdown` and `Header` blocks by default. Host apps can register custom blocks implementing the `PageBuilderBlock` interface via `basecms.pages.builder.blocks` config. Pages toggle between markdown body and builder blocks via `use_builder` flag. Frontend rendering resolves each block's Blade view and passes block data as variables.
- **Block scaffolding**: Custom page-builder blocks can be scaffolded with `php artisan basecms:make-block {Name}`, which creates an app block class, matching Blade view, and config registration entry.
- **Sitemap**: Base `SitemapService` in the package generates sitemap from Posts, Pages, Categories; the app extends it to add Notes. Triggered automatically on content save when flat-file backup is enabled.
- **Per-site sitemap/static output**: Sitemap generation and static export operate one site at a time, using the site’s primary domain when available
- **Custom page templates**: Pages can specify a `template` field to use dedicated Blade views (e.g. `now`, `resume`)
- **Legacy redirects**: `/posts` and `/posts/{post}` redirect to `/blog` equivalents
- **Feeds**: 6 feed endpoints (Posts and Notes in RSS, Atom, and JSON formats), each serving 20 items

## Multi-Site Model

- `Privateer\Basecms\Models\Site` owns shared CMS content and has a stable `key`
- `Privateer\Basecms\Models\Domain` stores hostnames for each site and marks one as primary
- Posts, pages, categories, assets, visits, and notes all belong to a site
- Public requests use the current domain to determine the site when `basecms.multisite.enabled=true`
- Unknown domains return `404` in multi-site mode
- Single-site mode still requires a `sites` record; Base CMS uses the first one

## Content Layout

Flat-file backups and reseeding now use a portable site-first directory structure:

```text
content/
  default/
    posts/
    pages/
    categories/
    notes/
```

Additional sites follow the same shape, for example `content/client-a/posts/...`.

The app reseeds content with:

```bash
php artisan app:re-seed-content
```

This truncates site-owned CMS tables, recreates the default site if needed, and rebuilds the database from the site-first content folders.

## Package Boundary

- `packages/privateer/basecms`: posts, pages, categories, metadata, assets, visits, configurable controllers/routes, shared services (including base SitemapService, VisitClassifier), page builder blocks, Filament panel, analytics widgets, `basecms:generate-sitemap` and `basecms:reclassify-visits` commands
- `app/Models/Note.php` and related app code: Notes, SitemapService extension (adds Notes), `app:re-seed-content` command, and any future custom content types
- `resources/views`: all public-facing templates remain app-owned
- `routes/web.php`: app composes custom routes first, then registers package CMS routes so Notes win before the wildcard page route

For package installation, config, and extension details, see [packages/privateer/basecms/README.md](/Users/phil/Herd/philstephens/packages/privateer/basecms/README.md).

To disable markdown backups locally, set `BASECMS_FLAT_FILE_BACKUP_ENABLED=false` in your environment.
