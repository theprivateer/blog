<?php

namespace Privateer\Basecms\Models;

use Spatie\LaravelMarkdown\MarkdownRenderer;

trait RendersBody
{
    public function render(): string
    {
        return app(MarkdownRenderer::class)
            ->toHtml($this->getAttribute('body'));
    }
}
