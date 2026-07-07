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
#[Description('Read a single entry of a registered content type by id or slug, including its rendered body and metadata.')]
class ReadContentTool extends Tool
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
            'id_or_slug' => $schema->string()->description('The numeric id or the slug of the entry to read.')->required(),
            'site' => $schema->string()->description('Optional site key to scope the query to (multisite installs only).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id_or_slug' => 'required|string',
            'site' => 'nullable|string',
        ]);

        $type = $validated['type'];

        if (! $this->registry()->has($type)) {
            return Response::error("Unknown content type [{$type}].");
        }

        if (! McpAccess::current()->can("{$type}:read")) {
            return $this->denied("{$type}:read");
        }

        $site = $this->resolveSite($validated['site'] ?? null);
        $model = $this->findContent($type, $validated['id_or_slug'], $site);

        if ($model === null) {
            return Response::error("No [{$type}] entry found for [{$validated['id_or_slug']}].");
        }

        $columns = array_values(array_unique(array_merge(
            ['id', 'slug'],
            $this->registry()->frontmatterColumnsFor($type),
        )));

        $payload = $model->only($columns);

        if ($this->registry()->supportsRenderFor($type)) {
            $payload['rendered_body'] = $model->render();
        }

        if ($this->registry()->supportsMetadataFor($type)) {
            $metadata = $model->metadata()->first();
            $payload['metadata'] = $metadata?->only(['title', 'description']);
        }

        return Response::structured($payload);
    }
}
