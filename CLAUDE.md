# Phil Stephens - Personal Blog & Portfolio

## Project Overview

This is a content-focused personal website built with Laravel 13. It features blog posts, notes, pages, and categories — all managed via a Filament v5 admin panel and backed up as markdown flat files in `/content`.

The bulk of the CMS logic lives in a local package at `packages/privateer/basecms` (installed via Composer path repository as `privateer/basecms`). The app layer keeps Notes, Blade templates, sitemap composition, feed configuration, and route ordering.

## Package Boundary

### `packages/privateer/basecms` (the package)

- **Models**: `Post`, `Page`, `Category`, `Metadata`, `Asset`, `Visit`, `McpToken` — all under `Privateer\Basecms\Models\`
- **Traits/interfaces**: `RendersBody`, `HasSlug`, `BacksUpToFlatFile`, `PageBuilderBlock`
- **Controllers**: `PostController`, `PageController`, `CategoryController` — configurable via `basecms.controllers` config; registered via `BasecmsRoutes::register()`
- **Events**: `PostSaved`, `PostDeleted` (used for all content types, not just posts)
- **Listener**: `FlatFileBackupListener` — writes/removes markdown files and triggers sitemap regeneration
- **Services**: `FlatFileBackupService`, `SitemapService` (base class), `VisitTrackingService`, `VisitClassifier`, `VisitAnalyticsSnapshot`, `MarkdownEditorAssetService`, `PageBuilderBlocks` (block resolution utility)
- **Middleware**: `TrackWebsiteVisits`, `AuthenticateMcpRequest` (bearer access key → Passport OAuth fallback → 401)
- **MCP**: `BasecmsMcpServer` (`Mcp/`) with generic, registry-driven tools in `Mcp/Tools/` (List/Read/Create/Update/Delete + 3 analytics tools); `ContentTypeRegistry` and `McpAccess` in `Mcp/Support/` — see "MCP Server" below
- **Filament**: `BasecmsPanelProvider`, resources for Posts/Pages/Categories/`McpToken`, dashboard widgets (`VisitAnalyticsOverview`, `TopVisitedPaths`, `VisitClassificationBreakdown`)
- **Migrations and factories** for all package models
- **Page builder**: `PageBuilderBlock` interface with `schema()` and `view()` methods; default `MarkdownBlock` and `HeaderBlock`; `PageBuilderBlocks` resolution service
- **Commands**: `GenerateSitemap` (`basecms:generate-sitemap`), `ReclassifyVisits` (`basecms:reclassify-visits`), `ManageMcpToken` (`basecms:mcp-token {create|list|revoke}`)
- **Config**: `config/basecms.php` — model classes, controllers, services, view names, flat-file backup toggle, visit tracking toggle, page builder blocks, markdown editor disk, Filament discovery paths, panel id/path, `mcp.*` (enabled flag, web route, local handle, OAuth toggle/default abilities, `content_types` registry)

### `app/` (the host application)

- **Models**: `User` (authentication, `FilamentUser`), `Note` (short-form content with optional external `link`, implements `Feedable` and `BacksUpToFlatFile`)
- **Controllers**: `NoteController`
- **Services**: `SitemapService` — extends the package base class, adds Note URLs to the sitemap
- **Filament**: `NoteResource` with form/table schemas and CRUD pages (auto-discovered into the package-owned panel)
- **Artisan commands**: `ReSeedContent` (`app:re-seed-content`)
- **MCP registration**: `config/basecms.php` registers `notes` in `mcp.content_types` (pointing at `App\Models\Note`) so Notes are reachable through the same MCP tools as package content, and sets `mcp.oauth.enabled` default to `true` for this project
- **Factories**: `UserFactory`, `NoteFactory` (in `database/factories/`)
- **Views**: All public-facing Blade templates remain app-owned
- **Route composition**: `routes/web.php` registers Notes and feeds before `BasecmsRoutes::register()` so the wildcard page route stays last

## Content Architecture

- **Dual storage**: All content lives in the database (SQLite) and syncs to markdown files with YAML frontmatter in `/content/{site}/{posts,notes,pages,categories}/` (site-first layout; single-site installs use `/content/default/...`)
- **Event-driven sync**: `PostSaved`/`PostDeleted` events trigger `FlatFileBackupListener` which writes/removes markdown files and regenerates the sitemap
- **Re-seeding**: `php artisan app:re-seed-content` truncates content tables and rebuilds the database from flat files via `DatabaseSeeder`
- **Filename conventions**: Posts use `{published_at_ISO}.{slug}.md` (or `{slug}.md` if unpublished), Notes use `{created_at_ISO}.{slug}.md`, Pages/Categories use `{slug}.md`

## Key Models & Relationships

- `Post` (package) → `belongsTo(Category)`, `morphOne(Metadata)` — uses `published()` scope for public display
- `Note` (app) — short-form content with optional external `link`, implements `Feedable`
- `Page` (package) — supports `is_homepage`, `draft`, and custom `template` fields; `morphOne(Metadata)`
- `Category` (package) → `morphOne(Metadata)` — organises posts
- `Metadata` (package) — polymorphic SEO (title, description) on Posts, Pages, Categories
- `Asset` (package) — tracks file uploads from markdown editors; polymorphic `attachable()` (links to any content model), `uploadedBy()` belongsTo User
- `Visit` (package) — analytics tracking (path, method, IP, session, user-agent, visitor classification via `visitor_type`, `visitor_label`, `classification_source`)
- `User` (app) — authentication, implements `FilamentUser` for admin access

Content models use traits `RendersBody` and `HasSlug`, implement the `BacksUpToFlatFile` interface (`getDiskName()`, `getFrontmatterColumns()`, `getFlatFileFilename()`), and where applicable implement `Feedable` (spatie/laravel-feed). Morph types use the package namespace (`Privateer\Basecms\Models\Post`, etc.).

## Routes

- `/` — Homepage (page where `is_homepage=true`), with 5 latest published posts
- `/blog` — Paginated post listing (simple pagination)
- `/blog/{post}` — Individual post (slug route binding)
- `/notes`, `/notes/{note}` — Notes listing and detail (simple pagination)
- `/category/{category}` — Posts filtered by category (simple pagination)
- `/{page}` — Wildcard catch-all for pages (aborts if `draft=true`)
- `/feed/posts/{format}`, `/feed/notes/{format}` — RSS, Atom, JSON feeds (20 items each)
- `/posts`, `/posts/{post}` — Legacy redirects to `/blog` equivalents

Routes from the package are registered via `BasecmsRoutes::register()` in `routes/web.php`. App-specific routes (Notes, feeds) are registered first so they take priority over the wildcard page route.

## Filament Admin (`/admin`)

The panel is owned by the package (`BasecmsPanelProvider`). It auto-discovers package resources (Posts, Pages, Categories) and widgets, plus app-specific Filament code from paths configured in `config/basecms.php`.

Resources: `PostResource`, `PageResource`, `CategoryResource` (package) and `NoteResource` (app) — each with extracted form/table schemas in `Schemas/` and `Tables/` subdirectories. MarkdownEditor fields use S3 disk for image attachments via `MarkdownEditorAssetService` (creates `Asset` records). Metadata relationship managed inline via nested Section.

Dashboard widgets: `VisitAnalyticsOverview` (total/unique visits, daily average), `TopVisitedPaths` (most visited pages table), and `VisitClassificationBreakdown` (visitor type breakdown — human vs bots), all powered by `VisitAnalyticsSnapshot` service with configurable time-window filters.

`McpTokenResource` (package, under **MCP Access Keys**) creates/revokes access keys; the create modal reveals the plaintext key exactly once via a persistent notification (it is never stored or shown again — only its SHA-256 hash is persisted on `McpToken`).

## MCP Server

The package registers a Model Context Protocol server (`Privateer\Basecms\Mcp\BasecmsMcpServer`) from `BasecmsServiceProvider::bootMcp()` — no app `routes/ai.php` is used.

- **Web (remote)**: `POST {basecms.mcp.web_route}` (default `/mcp`), guarded by `AuthenticateMcpRequest` middleware. Auth order: bearer `McpToken` (hashed lookup) → Passport OAuth (`auth('api')`, only if `basecms.mcp.oauth.enabled`) → `401`.
- **Local (stdio)**: `php artisan mcp:start {basecms.mcp.local_handle}` (default handle `basecms`). No auth layer — `McpAccess::current()` returns full access when nothing is bound in the container (trusted local shell).
- **Content-type registry** (`ContentTypeRegistry`): reads `config('basecms.mcp.content_types')`; derives writable fields from each model's `$fillable` (minus `site_id`), metadata support from `method_exists($model, 'metadata')`, and list/read columns from `getFrontmatterColumns()` — no separate field lists to keep in sync.
- **Tools** (`Mcp/Tools/`): `ListContentTool`, `ReadContentTool`, `CreateContentTool`, `UpdateContentTool`, `DeleteContentTool` (all generic, take a `type` argument), plus `AnalyticsOverviewTool`, `AnalyticsTopPathsTool`, `AnalyticsClassificationTool`. Each tool implements `shouldRegister()` so a key missing every relevant ability never sees the tool in `tools/list` at all (calling it directly then returns a JSON-RPC "not found" error, not an ability-denied message).
- **Abilities**: `{type}:read` / `{type}:write` / `{type}:delete` per registered type, `analytics:read`, or `*` for full access. OAuth-authenticated sessions get `basecms.mcp.oauth.default_abilities` (this project defaults to `['*']`).
- **Site scoping**: content tools resolve a `Site` via an optional `site` argument (looked up by `key`) or `SiteManager::required()`; analytics tools use `SiteManager::runFor()` since `VisitAnalyticsSnapshot` doesn't accept a site override directly.
- Writes go through the model's normal `save()`/`delete()`, so `PostSaved`/`PostDeleted` still fire and flat-file backup/sitemap regeneration stay in sync.

## Frontend

- Blade templates with `<x-site-layout>` wrapper component (passes `:metadata` prop for SEO)
- KelpUI (v1, CDN) for base styling
- Tailwind CSS v4 for utility classes
- Inclusive Sans font (Google Fonts)
- Minimal JavaScript — server-rendered pages
- Custom page templates: `now`, `resume`, `resume-leadership` (selected via Page `template` field)

## Services

- `FlatFileBackupService` (package) — syncs models to/from markdown files via a single unified `content` Storage disk (`config/filesystems.php`, rooted at `base_path('content')`); paths are site-first: `content/{site.key|default}/{table}/{filename}`
- `SitemapService` (package base + app extension) — base class in the package generates sitemap from Posts, Pages, Categories; app subclass overrides `extendSitemap()` to add Notes; registered in `basecms.services.sitemap` config so the package listener can trigger it
- `VisitTrackingService` (package) — optional analytics (`BASECMS_TRACK_VISITS=true`), skips authenticated users; classifies each visit via `VisitClassifier` at record time; registered via `TrackWebsiteVisits` middleware appended to `web` group in `bootstrap/app.php`
- `VisitClassifier` (package) — classifies visits as `likely_human`, `ai_crawler` (GPTBot, ClaudeBot, PerplexityBot, etc.), `search_crawler` (Googlebot, Bingbot, etc.), `other_bot`, or `unknown`; uses `jaybizzle/crawler-detect` plus a hardcoded AI rules layer
- `VisitAnalyticsSnapshot` (package) — calculates visit totals, unique visitors, daily averages, top paths, and classification breakdowns over a configurable rolling window
- `MarkdownEditorAssetService` (package) — handles file uploads from Filament MarkdownEditor components, stores files on S3 and creates `Asset` records tracking the upload

## Artisan Commands

- `php artisan app:re-seed-content` (app) — truncates content tables and re-seeds from `/content` markdown files
- `php artisan basecms:generate-sitemap` (package) — manually regenerates XML sitemap (also runs automatically on content save when flat-file backup is enabled)
- `php artisan basecms:reclassify-visits` (package) — re-runs visit classification on all stored visits in chunks of 250; useful after updating classifier rules
- `php artisan basecms:mcp-token {create|list|revoke}` (package) — manage MCP access keys; `create` prints the plaintext key once (`--name=`, `--abilities=`, `--expires=`, `--site=`)
- `php artisan mcp:start basecms` / `php artisan mcp:inspector /mcp|basecms` (from `laravel/mcp`) — run or interactively debug the MCP server

## Testing

- PHPUnit 12 (not Pest)
- Package model factories live in `packages/privateer/basecms/database/factories/`; app factories (`UserFactory`, `NoteFactory`) in `database/factories/`
- `PostFactory` has `published()`, `unpublished()`, `future()` states
- Comprehensive test coverage: controllers, models, Filament resources, services, middleware, listeners, feeds, commands, package integration, MCP server (`tests/Feature/Mcp/`, `tests/Feature/Middleware/AuthenticateMcpRequestTest.php`)
- MCP tool tests use `BasecmsMcpServer::tool(ToolClass::class, [...])->assertOk()/assertHasErrors()`; bind a restricted `McpAccess` via `app()->instance(McpAccess::class, new McpAccess([...]))` to test ability enforcement
- Seeders use `createQuietly()` to avoid event dispatch during seeding
- Run tests: `php artisan test --compact`

---

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- filament/filament (FILAMENT) - v5
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
