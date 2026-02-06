<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\MarkdownEditor;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                MarkdownEditor::make('body')
                    ->columnSpanFull(),

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
