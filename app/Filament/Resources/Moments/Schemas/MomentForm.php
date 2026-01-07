<?php

namespace App\Filament\Resources\Moments\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Schemas\Schema;

class MomentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                MarkdownEditor::make('body')
                    ->required()
                    ->fileAttachmentsDisk('s3')
                    ->columnSpanFull(),
            ]);
    }
}
