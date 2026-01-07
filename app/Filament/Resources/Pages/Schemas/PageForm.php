<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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
                MarkdownEditor::make('body')
                    ->fileAttachmentsDisk('s3')
                    ->columnSpanFull(),
                Toggle::make('is_homepage'),
                Toggle::make('draft'),
                TextInput::make('template'),
            ]);
    }
}
