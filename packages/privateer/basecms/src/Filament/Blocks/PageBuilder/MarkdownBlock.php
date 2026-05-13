<?php

namespace Privateer\Basecms\Filament\Blocks\PageBuilder;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Privateer\Basecms\Services\MarkdownEditorAssetService;

class MarkdownBlock implements PageBuilderBlock
{
    public function schema(): array
    {
        return [
            TextInput::make('_blockname')
                ->label('Block Name')
                ->columnSpanFull(),
            MarkdownEditorAssetService::configureEditor(
                MarkdownEditor::make('content')
                    ->columnSpanFull()
            ),
        ];
    }

    public function view(array $data = []): string
    {
        return 'basecms::blocks.page-builder.markdown';
    }
}
