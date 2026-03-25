<?php

namespace Tests\Fixtures\Filament\PageBuilder;

use Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlock;

class MissingViewBlock implements PageBuilderBlock
{
    public function schema(): array
    {
        return [];
    }

    public function view(): string
    {
        return 'basecms::blocks.page-builder.does-not-exist';
    }
}
