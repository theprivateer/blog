<?php

namespace App\Filament\Resources\Notes\Schemas;

use App\Services\MarkdownEditorAssetService;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
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
                MarkdownEditorAssetService::configureEditor(
                    MarkdownEditor::make('body')
                        ->fileAttachmentsDisk('s3')
                        ->columnSpanFull()
                ),
            ]);
    }
}
