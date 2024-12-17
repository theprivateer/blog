<?php

namespace App\ContentParsers;

use Spatie\Sheets\ContentParser;
use Illuminate\Support\HtmlString;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use Spatie\LaravelMarkdown\MarkdownRenderer;

class MarkdownWithFrontMatterParser implements ContentParser
{
    public function parse(string $contents): array
    {
        $document = YamlFrontMatter::parse($contents);

        $htmlContents = app(MarkdownRenderer::class)->toHtml($document->body());

        return array_merge(
            $document->matter(),
            ['contents' => new HtmlString($htmlContents)]
        );
    }
}
