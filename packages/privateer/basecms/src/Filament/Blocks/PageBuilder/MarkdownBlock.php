<?php

namespace Privateer\Basecms\Filament\Blocks\PageBuilder;

use Filament\Forms\Components\MarkdownEditor;
use Privateer\Basecms\Services\MarkdownEditorAssetService;

class MarkdownBlock implements PageBuilderBlock
{
    public function schema(): array
    {
        return [
            MarkdownEditorAssetService::configureEditor(
                MarkdownEditor::make('content')
                    ->columnSpanFull()
            ),
        ];
    }
}
