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
use Privateer\Basecms\Mcp\Tools\Concerns\InteractsWithContentTypes;

#[IsReadOnly]
#[Description('List entries of a registered content type (e.g. posts, pages, categories, notes), with optional search and pagination.')]
class ListContentTool extends Tool
{
    use InteractsWithContentTypes;

    public function shouldRegister(): bool
    {
        return McpAccess::current()->canAny(array_map(
            fn (string $type): string => "{$type}:read",
            $this->registry()->keys(),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $this->typeSchema($schema),
            'site' => $schema->string()->description('Optional site key to scope the query to (multisite installs only).'),
            'search' => $schema->string()->description('Optional case-insensitive search against the title.'),
            'page' => $schema->integer()->description('Page number.')->default(1),
            'per_page' => $schema->integer()->description('Results per page (max 50).')->default(15),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'site' => 'nullable|string',
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $type = $validated['type'];

        if (! $this->registry()->has($type)) {
            return Response::error("Unknown content type [{$type}].");
        }

        if (! McpAccess::current()->can("{$type}:read")) {
            return $this->denied("{$type}:read");
        }

        $site = $this->resolveSite($validated['site'] ?? null);
        $modelClass = $this->registry()->modelFor($type);

        $columns = array_values(array_unique(array_diff(
            array_merge(['id', 'slug'], $this->registry()->frontmatterColumnsFor($type)),
            ['body', 'blocks', 'intro'],
        )));

        $paginator = $modelClass::query()
            ->forSite($site)
            ->when(
                filled($validated['search'] ?? null),
                fn ($query) => $query->where('title', 'like', '%'.$validated['search'].'%'),
            )
            ->latest('updated_at')
            ->paginate(
                perPage: $validated['per_page'] ?? 15,
                page: $validated['page'] ?? 1,
            );

        return Response::structured([
            'items' => $paginator->getCollection()->map(fn ($item) => $item->only($columns))->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }
}
