<?php

namespace Privateer\Basecms\Mcp\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class ContentTypeRegistry
{
    /**
     * @return array<string, array{model: class-string<Model>, label: string}>
     */
    public function all(): array
    {
        return (array) config('basecms.mcp.content_types', []);
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    public function has(string $type): bool
    {
        return Arr::exists($this->all(), $type);
    }

    /**
     * @return array{model: class-string<Model>, label: string}
     */
    public function getOrFail(string $type): array
    {
        return $this->all()[$type] ?? throw new InvalidArgumentException(
            "Unknown MCP content type [{$type}]. Registered types: ".implode(', ', $this->keys()),
        );
    }

    /**
     * @return class-string<Model>
     */
    public function modelFor(string $type): string
    {
        return $this->getOrFail($type)['model'];
    }

    public function labelFor(string $type): string
    {
        return $this->getOrFail($type)['label'];
    }

    public function newModelFor(string $type): Model
    {
        $modelClass = $this->modelFor($type);

        return new $modelClass;
    }

    /**
     * Assignable columns for create/update, taken straight from the model's own
     * $fillable so the MCP surface never drifts from what the model itself allows.
     * site_id is excluded — MCP tools resolve the site via SiteManager instead.
     *
     * @return array<int, string>
     */
    public function writableFieldsFor(string $type): array
    {
        return array_values(array_diff($this->newModelFor($type)->getFillable(), ['site_id']));
    }

    /**
     * @return array<int, string>
     */
    public function frontmatterColumnsFor(string $type): array
    {
        $model = $this->newModelFor($type);

        return method_exists($model, 'getFrontmatterColumns') ? $model->getFrontmatterColumns() : [];
    }

    public function supportsMetadataFor(string $type): bool
    {
        return method_exists($this->modelFor($type), 'metadata');
    }

    public function supportsRenderFor(string $type): bool
    {
        return method_exists($this->modelFor($type), 'render');
    }

    /**
     * @return array<int, string>
     */
    public function abilitiesFor(string $type): array
    {
        return [
            "{$type}:read",
            "{$type}:write",
            "{$type}:delete",
        ];
    }

    /**
     * @return array<int, string>
     */
    public function allAbilities(): array
    {
        return array_merge(
            Arr::flatten(array_map(fn (string $type): array => $this->abilitiesFor($type), $this->keys())),
            ['analytics:read'],
        );
    }
}
