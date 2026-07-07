<?php

namespace Privateer\Basecms\Mcp\Tools\Concerns;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\JsonSchema\Types\StringType;
use Laravel\Mcp\Response;
use Privateer\Basecms\Mcp\Support\ContentTypeRegistry;
use Privateer\Basecms\Models\Site;

trait InteractsWithContentTypes
{
    use ResolvesMcpSite;

    protected function registry(): ContentTypeRegistry
    {
        return app(ContentTypeRegistry::class);
    }

    protected function typeSchema(JsonSchema $schema): StringType
    {
        return $schema->string()
            ->description('Registered content type key, e.g. '.implode(', ', $this->registry()->keys()).'.')
            ->enum($this->registry()->keys())
            ->required();
    }

    protected function denied(string $ability): Response
    {
        return Response::error("This access key does not have the [{$ability}] ability.");
    }

    protected function findContent(string $type, string $idOrSlug, Site $site): ?Model
    {
        $modelClass = $this->registry()->modelFor($type);

        $query = $modelClass::query()->forSite($site);

        return ctype_digit($idOrSlug)
            ? $query->find((int) $idOrSlug)
            : $query->where('slug', $idOrSlug)->first();
    }
}
