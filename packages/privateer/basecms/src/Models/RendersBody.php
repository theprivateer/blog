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

                    if ($resolvedBlock === null || ! View::exists($resolvedBlock->view())) {
                        return null;
                    }

                    return view($resolvedBlock->view(), [
                        'block' => $block,
                        'data' => data_get($block, 'data', []),
                        'page' => $this,
                    ])->render();
                })
                ->filter(fn (?string $renderedBlock): bool => filled($renderedBlock))
                ->implode(PHP_EOL);
        }

        return (string) app(MarkdownRenderer::class)
            ->toHtml((string) $this->getAttribute('body'));
    }
}
