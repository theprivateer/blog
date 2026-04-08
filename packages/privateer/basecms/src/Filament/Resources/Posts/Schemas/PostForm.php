<?php

namespace Privateer\Basecms\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\MarkdownEditorAssetService;
use Privateer\Basecms\Services\SiteManager;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug'),
                MarkdownEditorAssetService::configureEditor(
                    MarkdownEditor::make('body')
                        ->columnSpanFull()
                ),
                Textarea::make('intro')
                    ->columnSpanFull(),
                DateTimePicker::make('published_at'),
                Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query): Builder {
                            $site = filament()->getTenant();

                            if (! $site instanceof Site) {
                                $site = app(SiteManager::class)->required();
                            }

                            return $query->whereBelongsTo($site, 'site');
                        },
                    ),

                Section::make('Metadata')
                    ->relationship('metadata')
                    ->components([
                        TextInput::make('title'),
                        Textarea::make('description'),
                    ])->columnSpanFull(),
            ]);
    }
}
