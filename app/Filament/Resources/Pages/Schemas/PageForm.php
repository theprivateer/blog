<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Services\MarkdownEditorAssetService;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->readOnly(),
                MarkdownEditorAssetService::configureEditor(
                    MarkdownEditor::make('body')
                        ->fileAttachmentsDisk('s3')
                        ->columnSpanFull()
                ),
                Toggle::make('is_homepage'),
                Toggle::make('draft'),
                TextInput::make('template'),
                Section::make('Metadata')
                    ->relationship('metadata')
                    ->components([
                        TextInput::make('title'),
                        Textarea::make('description'),
                    ])->columnSpanFull(),
            ]);
    }
}
