<?php

namespace Privateer\Basecms\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Privateer\Basecms\Services\MarkdownEditorAssetService;

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
                    ->relationship(name: 'category', titleAttribute: 'title'),

                Section::make('Metadata')
                    ->relationship('metadata')
                    ->components([
                        TextInput::make('title'),
                        Textarea::make('description'),
                    ])->columnSpanFull(),
            ]);
    }
}
