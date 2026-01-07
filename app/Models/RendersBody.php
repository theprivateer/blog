<?php

namespace App\Models;

use Illuminate\Support\Str;

trait RendersBody
{
    public function render(): string
    {
        return app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)
            ->toHtml($this->getAttribute('body'));
    }
}
