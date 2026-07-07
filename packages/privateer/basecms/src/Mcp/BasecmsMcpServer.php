<?php

namespace Privateer\Basecms\Mcp;

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Privateer\Basecms\Mcp\Tools\AnalyticsClassificationTool;
use Privateer\Basecms\Mcp\Tools\AnalyticsOverviewTool;
use Privateer\Basecms\Mcp\Tools\AnalyticsTopPathsTool;
use Privateer\Basecms\Mcp\Tools\CreateContentTool;
use Privateer\Basecms\Mcp\Tools\DeleteContentTool;
use Privateer\Basecms\Mcp\Tools\ListContentTool;
use Privateer\Basecms\Mcp\Tools\ReadContentTool;
use Privateer\Basecms\Mcp\Tools\UpdateContentTool;

#[Name('Base CMS')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
    This server manages content (posts, pages, categories, notes, and any other
    registered content type) and read-only visit analytics for a Base CMS site.
    Every tool call is scoped by the access key's abilities; a call outside those
    abilities returns an error rather than partial data.
    MARKDOWN)]
class BasecmsMcpServer extends Server
{
    /**
     * @var array<int, class-string>
     */
    protected array $tools = [
        ListContentTool::class,
        ReadContentTool::class,
        CreateContentTool::class,
        UpdateContentTool::class,
        DeleteContentTool::class,
        AnalyticsOverviewTool::class,
        AnalyticsTopPathsTool::class,
        AnalyticsClassificationTool::class,
    ];
}
