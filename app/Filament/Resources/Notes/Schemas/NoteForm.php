<?php

namespace App\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                TextInput::make('slug')
                    ->readOnly(),
                TextInput::make('link')
                    ->columnSpanFull(),
                MarkdownEditor::make('body')
                    ->fileAttachmentsDisk('s3')
                    ->columnSpanFull(),
            ]);
    }
}
