<?php

namespace App\ContentParsers;

use Spatie\Sheets\ContentParser;
use Illuminate\Support\HtmlString;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\CommonMarkConverter;
use Spatie\YamlFrontMatter\YamlFrontMatter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;

class MarkdownWithFrontMatterParser implements ContentParser
{
    /** @var \League\CommonMark\CommonMarkConverter */
    // protected $commonMarkConverter;

    // public function __construct(CommonMarkConverter $commonMarkConverter)
    // {
    //     $this->commonMarkConverter = $commonMarkConverter;
    // }

    public function parse(string $contents): array
    {
        $document = YamlFrontMatter::parse($contents);

        $config = [];
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new FootnoteExtension());

        $converter = new MarkdownConverter($environment);
        $htmlContents = $converter->convert($document->body());

        return array_merge(
            $document->matter(),
            ['contents' => new HtmlString($htmlContents)]
        );
    }
}
