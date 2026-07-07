<?php

namespace Privateer\Basecms\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Arr;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Privateer\Basecms\Mcp\Support\McpAccess;
use Privateer\Basecms\Mcp\Tools\Concerns\InteractsWithContentTypes;
use Throwable;

#[Description('Update an existing entry of a registered content type by id or slug.')]
class UpdateContentTool extends Tool
{
    use InteractsWithContentTypes;

    public function shouldRegister(): bool
    {
        return McpAccess::current()->canAny(array_map(
            fn (string $type): string => "{$type}:write",
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
            'id_or_slug' => $schema->string()->description('The numeric id or the slug of the entry to update.')->required(),
            'fields' => $schema->object()->description('Key/value map of writable fields to update.')->required(),
            'site' => $schema->string()->description('Optional site key to scope the query to (multisite installs only).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'id_or_slug' => 'required|string',
            'fields' => 'required|array',
            'site' => 'nullable|string',
        ]);

        $type = $validated['type'];

        if (! $this->registry()->has($type)) {
            return Response::error("Unknown content type [{$type}].");
        }

        if (! McpAccess::current()->can("{$type}:write")) {
            return $this->denied("{$type}:write");
        }

        $site = $this->resolveSite($validated['site'] ?? null);
        $model = $this->findContent($type, $validated['id_or_slug'], $site);

        if ($model === null) {
            return Response::error("No [{$type}] entry found for [{$validated['id_or_slug']}].");
        }

        $writableFields = Arr::only($validated['fields'], $this->registry()->writableFieldsFor($type));

        if ($writableFields === []) {
            return Response::error("No writable fields were provided. Writable fields for [{$type}]: ".implode(', ', $this->registry()->writableFieldsFor($type)));
        }

        $model->fill($writableFields);

        try {
            $model->save();
        } catch (Throwable $e) {
            return Response::error("Failed to update [{$type}]: {$e->getMessage()}");
        }

        return Response::structured($model->only(array_values(array_unique(array_merge(
            ['id', 'slug'],
            $this->registry()->frontmatterColumnsFor($type),
        )))));
    }
}
