<?php

namespace Privateer\Basecms\Models;

use Illuminate\Support\Facades\View;
use Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlocks;
use Spatie\LaravelMarkdown\MarkdownRenderer;

trait RendersBody
{
    public function render(): string
    {
        if ($this->getAttribute('use_builder')) {
            return collect($this->getAttribute('blocks') ?? [])
                ->map(function (mixed $block): ?string {
                    if (! is_array($block)) {
                        return null;
                    }

                    $type = data_get($block, 'type');

                    if (! is_string($type)) {
                        return null;
                    }

                    $resolvedBlock = PageBuilderBlocks::resolve($type);

                    $data = data_get($block, 'data', []);

                    if (! is_array($data)) {
                        $data = [];
                    }

                    // Skip blocks whose view no longer exists (e.g. a block type was removed)
                    // rather than throwing. The view name can vary per data payload, so it's
                    // checked at render time rather than registration time.
                    if ($resolvedBlock === null || ! View::exists($resolvedBlock->view($data))) {
                        return null;
                    }

                    return view($resolvedBlock->view($data), $data)->render();
                })
                // filter() removes nulls from unresolvable blocks so they don't produce blank lines.
                ->filter(fn (?string $renderedBlock): bool => filled($renderedBlock))
                ->implode(PHP_EOL);
        }

        return (string) app(MarkdownRenderer::class)
            ->toHtml((string) $this->getAttribute('body'));
    }
}
