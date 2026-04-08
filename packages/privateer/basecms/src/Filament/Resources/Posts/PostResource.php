<?php

namespace Privateer\Basecms\Filament\Resources\Posts;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Privateer\Basecms\Filament\Resources\Posts\Pages\CreatePost;
use Privateer\Basecms\Filament\Resources\Posts\Pages\EditPost;
use Privateer\Basecms\Filament\Resources\Posts\Pages\ListPosts;
use Privateer\Basecms\Filament\Resources\Posts\Schemas\PostForm;
use Privateer\Basecms\Filament\Resources\Posts\Tables\PostsTable;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Services\SiteManager;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModel(): string
    {
        return (string) config('basecms.models.post', parent::getModel());
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
