<?php

namespace Privateer\Basecms\Filament\Resources\Domains;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Privateer\Basecms\Filament\Resources\Domains\Pages\ManageDomains;
use Privateer\Basecms\Filament\Resources\Domains\Schemas\DomainForm;
use Privateer\Basecms\Filament\Resources\Domains\Tables\DomainsTable;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Services\SiteManager;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'domain';

    public static function getModel(): string
    {
        return (string) config('basecms.models.domain', parent::getModel());
    }

    public static function form(Schema $schema): Schema
    {
        return DomainForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DomainsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereBelongsTo(app(SiteManager::class)->required(), 'site');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDomains::route('/'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('basecms.multisite.enabled', false);
    }

    public static function canAccess(): bool
    {
        return (bool) config('basecms.multisite.enabled', false);
    }
}
