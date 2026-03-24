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

Posts, pages, and categories are provided by the `privateer/basecms` local package. Notes remain app-specific. Flat-file backups are optional at the package level and enabled by default in this project via `BASECMS_FLAT_FILE_BACKUP_ENABLED=true`, so content continues to sync to markdown files in `/content` with YAML frontmatter.

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
php artisan app:generate-sitemap       # Regenerate XML sitemap
php artisan app:re-seed-content    # Re-seed database from /content markdown files
php artisan test --compact         # Run test suite
vendor/bin/pint --dirty            # Format changed PHP files
```

## Admin Panel

Filament admin at `/admin` is owned by the Base CMS package. The package registers the shared CMS resources for posts, pages, and categories, then discovers app-specific Filament code for Notes. Markdown editor uploads are stored on S3 and tracked via the `Asset` model. The dashboard includes visit analytics widgets showing traffic stats over a 7-day window.

## Architecture

- **Package split**: Shared CMS code lives in `packages/privateer/basecms`; the app keeps Notes, Blade templates, feed composition, and route composition
- **Flat-file backup**: Optional package feature controlled by `basecms.flat_file_backup.enabled`; this project enables it by default so shared CMS content syncs to `/content/{type}/` markdown files
- **Polymorphic metadata**: SEO title/description stored via `Metadata` on Posts, Pages, and Categories
- **Visit tracking**: Optional analytics (enable via `BASECMS_TRACK_VISITS=true` in `.env`), skips authenticated users and `livewire-*` requests
- **Slug generation**: Automatic via spatie/laravel-sluggable
- **Markdown rendering**: spatie/laravel-markdown with Shiki syntax highlighting (`github-dark` theme), auto-anchored headings, and GitHub-flavored markdown extensions
- **Asset tracking**: File uploads from markdown editors are stored on S3 and tracked via polymorphic `Asset` model
- **Custom page templates**: Pages can specify a `template` field to use dedicated Blade views (e.g. `now`, `resume`)
- **Legacy redirects**: `/posts` and `/posts/{post}` redirect to `/blog` equivalents
- **Feeds**: 6 feed endpoints (Posts and Notes in RSS, Atom, and JSON formats), each serving 20 items

## Package Boundary

- `packages/privateer/basecms`: posts, pages, categories, metadata, assets, visits, shared controllers/routes, shared services, Filament panel, analytics widgets
- `app/Models/Note.php` and related app code: Notes and any future custom content types
- `resources/views`: all public-facing templates remain app-owned
- `routes/web.php`: app composes custom routes first, then registers package CMS routes so Notes win before the wildcard page route

For package installation, config, and extension details, see [packages/privateer/basecms/README.md](/Users/phil/Herd/philstephens/packages/privateer/basecms/README.md).

To disable markdown backups locally, set `BASECMS_FLAT_FILE_BACKUP_ENABLED=false` in your environment.
