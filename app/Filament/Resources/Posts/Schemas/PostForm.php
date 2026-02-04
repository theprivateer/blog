<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                MarkdownEditor::make('body')
                    ->fileAttachmentsDisk('s3')
                    ->columnSpanFull(),
                Textarea::make('intro')
                    ->columnSpanFull(),
                DateTimePicker::make('published_at'),

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
