<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\MarkdownEditor;

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
                MarkdownEditor::make('body')
                    ->fileAttachmentsDisk('s3')
                    ->columnSpanFull(),
                Toggle::make('is_homepage'),
                Toggle::make('draft'),
                TextInput::make('template'),
                Section::make('Metadata')
                    ->relationship('metadata')
                    ->schema([
                        TextInput::make('title'),
                        Textarea::make('description'),
                        // FileUpload::make('image'),
                    ])->columnSpanFull(),
            ]);
    }
}
