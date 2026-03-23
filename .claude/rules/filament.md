---
paths: "app/Filament/**/*.php"
---

# Filament v5 / Livewire v4 Rules

### Version Info
- Filament v5 beta = v4 + Livewire v4 support (no functional changes)
- Requires: PHP 8.2+, Laravel 11.28+, Livewire 4.0@beta
- Always run `php artisan filament:optimize` and `php artisan icons:cache` in production

### Filament v5 Breaking Changes (from v4)

**Actions namespace moved:**
```php
// ❌ Old (v4)
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

// ✅ New (v5)
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
```

**Form method signature changed:**
```php
// ❌ Old (v4)
use Filament\Forms\Form;
public static function form(Form $form): Form {
    return $form->schema([...]);
}

// ✅ New (v5)
use Filament\Schemas\Schema;
public static function form(Schema $schema): Schema {
    return $schema->components([...]);
}
```

### File Structure
```
app/Filament/Resources/
└── Posts/
    ├── PostResource.php
    ├── Pages/{Create,Edit,List}Post.php
    ├── Schemas/PostForm.php      # Extract form logic here
    └── Tables/PostsTable.php     # Extract table logic here
```
Multi-panel: `app/Filament/{Admin,App}/Resources/`

### Performance Rules

**Tables:**
- ALWAYS use `->deferLoading()` on tables with 50+ records
- Use `simplePaginate()` or `cursorPaginate()` for 1000+ records
- Filament auto-eager-loads dot-notation columns (`author.name`)
- For complex queries: `->modifyQueryUsing(fn ($q) => $q->with([...]))`

**Forms:**
- NEVER use bare `->live()` on text inputs (fires every keystroke)
- Use `->live(onBlur: true)` or `->live(debounce: 500)` instead
- Use `->partiallyRenderAfterStateUpdated()` to skip full form re-render
- Use `->afterStateUpdatedJs()` for client-side updates without server roundtrip

**Widgets:**
- Defer expensive widgets: set `public bool $readyToLoad = false`, trigger via `wire:init="loadData"`

### Livewire v4 Patterns

**Computed properties** (replace complex public properties):
```php
#[Computed]
public function stats() { return $this->calculate(); }

#[Computed(persist: true, seconds: 300)]  // Cached
public function heavyData() { ... }
```

**Islands** for isolated updates: `@island(lazy: true)` or `@island(poll: '30s')`

**Navigation:** Use `wire:navigate` on links, `->spa()` on panel config

**Locked properties:** `#[Locked] public int $orderId;` (hidden from frontend)

### Component Patterns

**Forms:** Use `Grid`, `Section::make()->collapsible()`, `Tabs->persistTabInQueryString()`, `Wizard` for steps. Extract to reusable classes extending layout components.

**Tables:** Set `->searchable()` and `->sortable()` explicitly. Use `SelectFilter::make()->multiple()`. Group bulk actions in `BulkActionGroup::make()`.

**Actions:** Use `->slideOver()` for complex forms, `->requiresConfirmation()` on destructive actions, `->deselectRecordsAfterCompletion()` on bulk actions.

### Multi-Panel
- Each panel needs unique `->id()`, `->path()`, discovery paths
- Use shared plugin/helper for common config across panels

### Panel Access Control

**Use FilamentUser contract** (not custom middleware):
```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
        // Or for multiple panels:
        // return match ($panel->getId()) {
        //     'admin' => $this->is_admin,
        //     'app' => $this->hasVerifiedEmail(),
        //     default => false,
        // };
    }
}

### Panel Layout Configuration

**Sidebar and content width:**
```php
use Filament\Support\Enums\Width;

return $panel
    ->sidebarWidth('14rem')           // Compress sidebar (default ~18rem)
    ->maxContentWidth(Width::Full);   // Full-width tables/content
```

**Available Width values:** `ExtraSmall`, `Small`, `Medium`, `Large`, `ExtraLarge`, `TwoExtraLarge`...`SevenExtraLarge`, `Full`, `Screen`

**Note:** In Filament v5 beta, use `Filament\Support\Enums\Width`—there is no `MaxWidth` enum.

### Testing
```php
Filament::setCurrentPanel(Filament::getPanel('admin'));

livewire(ListPosts::class)->assertCanSeeTableRecords($posts);
livewire(CreatePost::class)->fillForm([...])->call('create')->assertHasNoFormErrors();
```

### Custom Themes (Tailwind v4)

**ALWAYS use the artisan command** to scaffold themes—don't create manually:

```bash
php artisan make:filament-theme admin
```

This generates `resources/css/filament/admin/theme.css`:
```css
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
```

**Required setup:**
1. Add to `vite.config.ts` input array: `'resources/css/filament/admin/theme.css'`
2. Command auto-adds `->viteTheme('resources/css/filament/admin/theme.css')` to panel provider

**Why this matters:** Without `@import` of Filament's base theme and `@source` directives, Tailwind v4 purges all Filament classes in production builds, breaking the entire admin UI.

**Custom styles:** Add your CSS after the `@source` directives in theme.css.

### Pitfalls
- NEVER pass Eloquent models as public Livewire properties—use IDs + computed
- Don't nest Livewire components >1 level in panels—use relation managers
- Always debounce live search inputs (minimum 300ms)
- Don't skip `filament:optimize` in production
- NEVER manually create Filament theme CSS—always use `make:filament-theme` command
- Use `Width` enum (not `MaxWidth`) for `->maxContentWidth()` in Filament v5 beta
