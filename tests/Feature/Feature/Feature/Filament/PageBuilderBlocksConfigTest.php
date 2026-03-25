<?php

namespace Tests\Feature\Feature\Feature\Filament;

use Illuminate\Support\Facades\View;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlocks;
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

    public function test_default_blocks_expose_package_view_names(): void
    {
        $this->assertSame('basecms::blocks.page-builder.markdown', app(MarkdownBlock::class)->view());
        $this->assertSame('basecms::blocks.page-builder.header', app(HeaderBlock::class)->view());
    }

    public function test_page_builder_block_names_and_labels_follow_convention(): void
    {
        $this->assertSame('markdown', PageBuilderBlocks::nameForClass(MarkdownBlock::class));
        $this->assertSame('Markdown', PageBuilderBlocks::labelForClass(MarkdownBlock::class));
        $this->assertSame('header', PageBuilderBlocks::nameForClass(HeaderBlock::class));
        $this->assertSame('Header', PageBuilderBlocks::labelForClass(HeaderBlock::class));
    }

    public function test_package_block_views_exist(): void
    {
        $this->assertTrue(View::exists('basecms::blocks.page-builder.markdown'));
        $this->assertTrue(View::exists('basecms::blocks.page-builder.header'));
    }
}
