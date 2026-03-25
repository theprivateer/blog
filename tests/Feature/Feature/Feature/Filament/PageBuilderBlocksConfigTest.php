<?php

namespace Tests\Feature\Feature\Feature\Filament;

use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlock;
use Tests\TestCase;

class PageBuilderBlocksConfigTest extends TestCase
{
    public function test_page_builder_default_blocks_are_configured(): void
    {
        $this->assertSame([
            MarkdownBlock::class,
            HeaderBlock::class,
        ], config('basecms.pages.builder.blocks'));
    }

    public function test_default_markdown_block_implements_page_builder_block_interface(): void
    {
        $this->assertInstanceOf(PageBuilderBlock::class, app(MarkdownBlock::class));
    }

    public function test_default_header_block_implements_page_builder_block_interface(): void
    {
        $this->assertInstanceOf(PageBuilderBlock::class, app(HeaderBlock::class));
    }
}
