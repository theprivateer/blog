# Phil Stephens

Personal blog and portfolio site built with Laravel 13.

Shared CMS functionality lives in the local package [packages/privateer/basecms](packages/privateer/basecms/README.md) (`privateer/basecms`), installed via a Composer path repository. This app keeps the site presentation layer, Notes, feed/route composition, and app-specific extensions around the package.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.4
- **Admin**: Filament v5 (Livewire v4, Alpine.js)
- **Frontend**: Blade templates, KelpUI, Tailwind CSS v4, Vite 7
- **Content**: Database-backed content with optional markdown flat-file backups
- **Feeds**: RSS, Atom, JSON via spatie/laravel-feed
- **SEO**: Auto-generated sitemap via spatie/laravel-sitemap
- **AI agent access**: Model Context Protocol server via laravel/mcp, with laravel/passport for OAuth
- **Storage**: SQLite (local), AWS S3 (images)
- **Testing**: PHPUnit 12

## Features

- **Posts, Pages, Categories, Notes** — database-backed content with optional markdown flat-file backups and YAML frontmatter
- **Filament admin panel** (`/admin`) — CRUD for all content types, visit analytics dashboard, domain management, MCP access key management
- **Multi-site support** — optional tenancy across multiple domains/sites, each with its own content, analytics, and admin scoping
- **Visit analytics** — traffic tracking with visitor classification (human, AI crawler, search crawler, other bot); disabled in this project in favour of [Fathom Analytics](#analytics)
- **Page builder** — optional block-based page editing alongside traditional markdown bodies
- **AI-assisted meta descriptions** — manual or bulk SEO description generation for Posts and Pages via the Laravel AI SDK
- **Static site export** — render the live app into a static output directory
- **MCP server** — remote (HTTP) and local (stdio) Model Context Protocol servers so AI agents can read, create, update, and delete content, and read analytics, via scoped access keys or OAuth
- **Feeds** — RSS, Atom, and JSON for Posts and Notes
- **Sitemap** — auto-regenerated on content save

## Project Structure

```text
app/                        App-owned code
  Filament/Resources/Notes/  Filament resource for Notes
  Http/Controllers/          NoteController, PageController override
  Models/                    User, Note, Visit (extends package Visit + Prunable)
  Services/                  SitemapService (extends package base, adds Notes)
  StaticSite/                NoteStaticRouteExporter
config/basecms.php          Host-app overrides for the package config
content/                    Flat-file markdown backups, one folder per site
packages/privateer/basecms/ Local Composer package — shared CMS (see its own README)
resources/views/            All public-facing Blade templates (app-owned)
routes/web.php               App routes (Notes, feeds, legacy redirects) + BasecmsRoutes::register()
routes/api.php               Passport's default authenticated /api/user route
tests/                       PHPUnit feature/unit tests for both app and package code
```

## Getting Started

### Prerequisites

- PHP 8.4
- Composer
- Node.js (for Vite/Tailwind)
- SQLite (default local database)
- [Laravel Herd](https://herd.laravel.com) (recommended — serves the app automatically) or `php artisan serve`

### Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan basecms:install   # creates the first admin user and site
npm run build                 # or `npm run dev` for local asset watching
```

If the local package has changed, refresh it with:

```bash
composer update privateer/basecms --no-interaction
```

### Environment Variables

Beyond Laravel's standard `.env` variables (`APP_*`, `DB_*`, mail, queue, etc.), this project reads:

| Variable | Purpose |
|---|---|
| `BASECMS_FLAT_FILE_BACKUP_ENABLED` | Sync content to `/content` markdown files on save (enabled by default in this project) |
| `BASECMS_TRACK_VISITS` | Enable the package's in-app visit tracking middleware and analytics dashboard (disabled in this project — see [Analytics](#analytics)) |
| `BASECMS_PAGE_BUILDER_ENABLED` | Enable the block-based page builder editor |
| `BASECMS_MULTISITE_ENABLED` | Enable multi-site tenancy |
| `BASECMS_GENERATE_META_DESCRIPTIONS_ENABLED` | Enable the AI meta description action/command |
| `BASECMS_STATIC_SITE_ENABLED` / `BASECMS_STATIC_SITE_BASE_URL` | Static site export settings |
| `BASECMS_MCP_ENABLED` | Toggle the MCP server (web + local) — defaults to enabled |
| `BASECMS_MCP_ROUTE` | Path for the web MCP endpoint — defaults to `/mcp` |
| `BASECMS_MCP_OAUTH` | Enable the OAuth connector flow for the MCP server (enabled by default in this project) |
| `CONTACT_PHONE_NUMBER` | Displayed on contact-related pages |
| `FATHOM_SITE_ID` | [Fathom Analytics](#analytics) site ID — snippet is omitted if unset |
| `AWS_*` | S3 credentials for markdown editor image uploads |

### Running Locally

Served by Laravel Herd at `https://philstephens.test`, or run manually:

```bash
composer run dev   # concurrently runs the dev server, queue listener, log tailer, and Vite
```

## Usage

### Content Types

| Type | Description |
|------|-------------|
| **Posts** | Blog articles with categories, intros, and publish dates |
| **Notes** | Short-form content with optional external links (app-owned) |
| **Pages** | Static pages (About, Now, Uses, Work, resume variants, etc.) with optional custom templates |
| **Categories** | Organisational tags for posts |

Posts, pages, and categories are provided by the `privateer/basecms` package. Notes remain app-specific but participate in the same flat-file backup and site-scoping mechanisms.

### Admin Panel

Filament admin at `/admin` is owned by the Base CMS package, which registers CRUD resources for posts, pages, and categories, then discovers app-specific Filament code for Notes and the MCP access key resource. The dashboard includes visit analytics widgets (traffic over a configurable time window, top paths, and a human/AI-crawler/search-crawler/bot breakdown).

When `basecms.multisite.enabled` is enabled, the panel uses Filament tenancy with `Site` as the tenant model and exposes a Domains resource per site. In this app, every admin user can access every site.

### MCP Server

The app exposes a Model Context Protocol server so AI agents can read, create, update, and delete content, and read analytics:

- **Remote (web)**: `POST /mcp` (configurable via `BASECMS_MCP_ROUTE`), authenticated via either a bearer access key or OAuth (Passport) — suited to hosted/connector use, e.g. the Claude web app.
- **Local (stdio)**: `php artisan mcp:start basecms`, for trusted local agent access with no auth required.
- **Content-type registry**: `posts`, `pages`, `categories` (package) and `notes` (app) are registered in `config/basecms.php` under `mcp.content_types`, each mapped to its Eloquent model.
- **Tools**: generic list/read/create/update/delete tools driven by the registry, plus three read-only analytics tools (overview, top paths, visitor classification).

Manage access keys with:

```bash
php artisan basecms:mcp-token create --name="Claude agent" --abilities=posts:read,posts:write,analytics:read
php artisan basecms:mcp-token list
php artisan basecms:mcp-token revoke {id}
```

Abilities are `{type}:read` / `{type}:write` / `{type}:delete` per registered content type, `analytics:read`, or `*` for full access. Keys can also be created from `/admin` under **MCP Access Keys** (the plaintext key is shown once, at creation time, and never stored). Debug a server interactively with `php artisan mcp:inspector /mcp` or `php artisan mcp:inspector basecms`.

### Multi-Site

- `Privateer\Basecms\Models\Site` and `Domain` scope content per website; the active site resolves from the request host when multi-site is enabled, or defaults to the first `sites` row otherwise.
- Posts, pages, categories, assets, visits, and notes all belong to a site and can reuse the same slug across sites.
- Additional sites are created with `php artisan basecms:create-site`.

### Content Layout

Flat-file backups use a site-first directory structure:

```text
content/
  default/
    posts/
    pages/
    categories/
    notes/
```

Reseed the database from these files with:

```bash
php artisan app:re-seed-content
```

### Page Builder & Static Export

- Optional block-based page editing (`BASECMS_PAGE_BUILDER_ENABLED=true`), with `Markdown` and `Header` blocks shipped by default. Scaffold a new block with `php artisan basecms:make-block Hero`.
- Export the rendered site to static files with `php artisan basecms:generate-static` (writes to `storage/app/static-site` by default).

### Useful Commands

```bash
php artisan basecms:install                      # First-time setup: admin user + first site
php artisan basecms:create-site                   # Create an additional site (multi-site mode)
php artisan basecms:generate-sitemap               # Regenerate XML sitemap
php artisan basecms:generate-static                # Export a static build of the site
php artisan basecms:generate-meta-descriptions {post|page} [--force]  # Bulk AI meta descriptions
php artisan basecms:reclassify-visits              # Re-classify all stored visits
php artisan basecms:make-block Hero                # Scaffold a custom page-builder block
php artisan basecms:mcp-token {create|list|revoke} # Manage MCP access keys
php artisan mcp:start basecms                      # Run the local (stdio) MCP server
php artisan mcp:inspector /mcp                     # Interactively inspect the MCP server
php artisan app:re-seed-content                    # Re-seed database from /content markdown files
```

### Visit Retention

Visit pruning is app-owned rather than package-owned: `App\Models\Visit` extends the package `Visit` model, adds Laravel's `Prunable` trait, and is pointed at from `basecms.models.visit`. This project prunes visits older than 30 days via a daily `model:prune` schedule in `bootstrap/app.php`.

### Analytics

This project uses [Fathom Analytics](https://usefathom.com) instead of the package's built-in visit tracking:

- `BASECMS_TRACK_VISITS` is set to `false`, so the package's `TrackWebsiteVisits` middleware and `Visit` model are inactive.
- The Fathom snippet is rendered by the `<x-fathom-analytics />` component (`resources/views/components/fathom-analytics.blade.php`), included in the `<head>` of every front-end template (`site-layout`, `resume`, `resume-leadership`, `resume-hardcoded`).
- The site ID comes from `FATHOM_SITE_ID` (`config('services.fathom.site_id')`) and the snippet only renders when that value is set **and** `app()->isProduction()` is `true` — it never loads locally or in staging/testing environments.

## Testing

```bash
php artisan test --compact              # Full suite (app + package tests)
php artisan test --compact tests/Feature/Mcp   # A specific directory or file
```

## Building

```bash
npm run build   # Production frontend assets (Vite)
npm run dev     # Local asset watching
```

## Architecture

- **Package split**: shared CMS code lives in `packages/privateer/basecms`; the app keeps Notes, Blade templates, feed/route composition, and MCP content-type registration for Notes
- **Configurable controllers/services**: package controllers and the sitemap service are swappable via `basecms.controllers` / `basecms.services` config
- **Polymorphic metadata**: SEO title/description stored via `Metadata` on Posts, Pages, and Categories
- **Slug generation**: automatic via spatie/laravel-sluggable, scoped per site
- **Markdown rendering**: spatie/laravel-markdown with Shiki syntax highlighting (`github-dark` theme) and auto-anchored headings
- **Asset tracking**: markdown editor uploads go to the disk in `basecms.markdown_editor.attachments_disk` (S3 in this project) and are tracked via the polymorphic `Asset` model
- **Custom page templates**: Pages can specify a `template` field to use dedicated Blade views (e.g. `now`, `resume`, `resume-leadership`)
- **Legacy redirects**: `/posts` and `/posts/{post}` redirect to `/blog` equivalents

For package installation, configuration, and extension details (multi-site internals, Filament tenancy, flat-file backup layout, and more), see [packages/privateer/basecms/README.md](packages/privateer/basecms/README.md).

## Package Boundary

- `packages/privateer/basecms`: posts, pages, categories, metadata, assets, visits, sites/domains, MCP server + tools + access keys, configurable controllers/routes, shared services, page builder blocks, Filament panel, analytics widgets, and the `basecms:*` commands
- `app/Models/Note.php` and related app code: Notes, `SitemapService` extension (adds Notes), `NoteStaticRouteExporter`, `app:re-seed-content`, and the `notes` MCP content-type registration
- `resources/views`: all public-facing templates remain app-owned
- `routes/web.php`: app composes custom routes first, then registers package CMS routes so Notes win before the wildcard page route
