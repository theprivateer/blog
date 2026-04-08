<?php

namespace Privateer\Basecms\Filament\Resources\Categories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Privateer\Basecms\Filament\Resources\Categories\Pages\CreateCategory;
use Privateer\Basecms\Filament\Resources\Categories\Pages\EditCategory;
use Privateer\Basecms\Filament\Resources\Categories\Pages\ListCategories;
use Privateer\Basecms\Filament\Resources\Categories\Schemas\CategoryForm;
use Privateer\Basecms\Filament\Resources\Categories\Tables\CategoriesTable;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Services\SiteManager;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModel(): string
    {
        return (string) config('basecms.models.category', parent::getModel());
    }

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereBelongsTo(app(SiteManager::class)->required(), 'site');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
