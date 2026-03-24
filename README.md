# Phil Stephens

Personal blog and portfolio site built with Laravel 12.

## Tech Stack

- **Backend**: Laravel 12, PHP 8.4
- **Admin**: Filament v5 (Livewire v4, Alpine.js)
- **Frontend**: Blade templates, KelpUI, Tailwind CSS v4, Vite 7
- **Content**: Markdown with YAML frontmatter (dual database + flat-file storage)
- **Feeds**: RSS, Atom, JSON via spatie/laravel-feed
- **SEO**: Auto-generated sitemap via spatie/laravel-sitemap
- **Storage**: SQLite (local), AWS S3 (images)
- **Testing**: PHPUnit v11

## Content Types

| Type | Description |
|------|-------------|
| **Posts** | Blog articles with categories, intros, and publish dates |
| **Notes** | Short-form content with optional external links |
| **Pages** | Static pages (About, Now, Uses, Work, etc.) with optional custom templates |
| **Categories** | Organisational tags for posts |

All content models back up to markdown files in `/content` with YAML frontmatter, enabling version-controlled content alongside database management via Filament. On save/delete, events trigger flat-file sync and sitemap regeneration automatically.

## Local Development

Served by [Laravel Herd](https://herd.laravel.com) at `https://philstephens.test`.

```bash
composer install
npm install
npm run dev          # or npm run build for production assets
php artisan migrate --seed
```

### Useful Commands

```bash
php artisan app:generate-sitemap   # Regenerate XML sitemap
php artisan app:re-seed-content    # Re-seed database from /content markdown files
php artisan test --compact         # Run test suite
vendor/bin/pint --dirty            # Format changed PHP files
```

## Admin Panel

Filament admin at `/admin` manages all content types. Resources include form schemas with markdown editors and S3 image uploads (tracked via `Asset` model). Each resource extracts form and table configuration into `Schemas/` and `Tables/` subdirectories. Dashboard includes visit analytics widgets showing traffic stats over a 7-day window.

## Architecture

- **Flat-file backup**: On save/delete, models sync to `/content/{type}/` markdown files and the sitemap regenerates automatically via `PostSaved`/`PostDeleted` events and `FlatFileBackupListener`
- **Polymorphic metadata**: SEO title/description stored via `Metadata` model on Posts, Pages, and Categories
- **Visit tracking**: Optional analytics (enable via `TRACK_VISITS=true` in `.env`), skips authenticated users
- **Slug generation**: Automatic via spatie/laravel-sluggable
- **Markdown rendering**: spatie/laravel-markdown with Shiki syntax highlighting (`github-dark` theme), auto-anchored headings, and GitHub-flavored markdown extensions
- **Asset tracking**: File uploads from markdown editors are stored on S3 and tracked via polymorphic `Asset` model
- **Custom page templates**: Pages can specify a `template` field to use dedicated Blade views (e.g. `now`, `resume`)
- **Legacy redirects**: `/posts` and `/posts/{post}` redirect to `/blog` equivalents
- **Feeds**: 6 feed endpoints (Posts and Notes in RSS, Atom, and JSON formats), each serving 20 items
