<?php

namespace Privateer\Basecms\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Privateer\Basecms\Services\MarkdownEditorAssetService;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $builderEnabled = (bool) config('basecms.pages.builder.enabled');

        $contentComponents = [
            MarkdownEditorAssetService::configureEditor(
                MarkdownEditor::make('body')
                    ->fileAttachmentsDisk('s3')
                    ->visible(fn (Get $get): bool => ! ($builderEnabled && (bool) $get('use_builder')))
                    ->columnSpanFull()
            ),
        ];

        if ($builderEnabled) {
            $contentComponents = [
                Toggle::make('use_builder')
                    ->label('Use builder')
                    ->live(),
                ...$contentComponents,
                Builder::make('blocks')
                    ->visible(fn (Get $get): bool => (bool) $get('use_builder'))
                    ->blocks([
                        Block::make('markdown')
                            ->label('Markdown')
                            ->schema([
                                MarkdownEditorAssetService::configureEditor(
                                    MarkdownEditor::make('content')
                                        ->fileAttachmentsDisk('s3')
                                        ->columnSpanFull()
                                ),
                            ]),
                    ])
                    ->columnSpanFull(),
            ];
        }

        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->readOnly(),
                ...$contentComponents,
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
