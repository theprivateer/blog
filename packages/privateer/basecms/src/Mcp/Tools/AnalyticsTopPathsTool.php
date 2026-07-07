<?php

namespace Privateer\Basecms\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Privateer\Basecms\Mcp\Support\McpAccess;
use Privateer\Basecms\Mcp\Tools\Concerns\InteractsWithAnalytics;

#[IsReadOnly]
#[Description('Read the most visited paths for a configurable time window.')]
class AnalyticsTopPathsTool extends Tool
{
    use InteractsWithAnalytics;

    public function shouldRegister(): bool
    {
        return McpAccess::current()->can('analytics:read');
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->filterSchema($schema),
            'limit' => $schema->integer()->description('Maximum number of paths to return (max 50).')->default(10),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if (! McpAccess::current()->can('analytics:read')) {
            return Response::error('This access key does not have the [analytics:read] ability.');
        }

        $filters = $this->validatedFilters($request);
        $limit = min((int) $request->get('limit', 10), 50);

        $topPaths = $this->withSnapshot(
            $filters['site'] ?? null,
            fn ($snapshot) => $snapshot->topPaths($limit, $filters)->all(),
        );

        return Response::structured(['top_paths' => $topPaths]);
    }
}
