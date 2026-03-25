<?php

namespace Privateer\Basecms\Filament\Blocks\PageBuilder;

interface PageBuilderBlock
{
    public function schema(): array;

    public function view(): string;
}
