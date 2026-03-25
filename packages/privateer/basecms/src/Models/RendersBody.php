<?php

namespace Privateer\Basecms\Models;

use Spatie\LaravelMarkdown\MarkdownRenderer;

trait RendersBody
{
    public function render(): string|array|null
    {
        if ($this->getAttribute('use_builder')) {
            return $this->getAttribute('blocks');
        }

        return app(MarkdownRenderer::class)
            ->toHtml($this->getAttribute('body'));
    }
}
