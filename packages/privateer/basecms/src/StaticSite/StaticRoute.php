<?php

namespace Privateer\Basecms\StaticSite;

class StaticRoute
{
    /**
     * @param  array<string, mixed>  $routeParameters
     */
    public function __construct(
        public readonly string $sourceUri,
        public readonly string $publicUri,
        public readonly string $outputPath,
        public readonly string $kind = 'html',
        public readonly ?string $routeName = null,
        public readonly array $routeParameters = [],
        public readonly ?string $redirectTo = null,
    ) {}

    /**
     * @param  array<string, mixed>  $routeParameters
     */
    public static function html(
        string $sourceUri,
        string $publicUri,
        string $outputPath,
        ?string $routeName = null,
        array $routeParameters = [],
    ): self {
        return new self(
            sourceUri: $sourceUri,
            publicUri: $publicUri,
            outputPath: $outputPath,
            kind: 'html',
            routeName: $routeName,
            routeParameters: $routeParameters,
        );
    }

    public static function artifact(
        string $sourceUri,
        string $publicUri,
        string $outputPath,
        ?string $routeName = null,
    ): self {
        return new self(
            sourceUri: $sourceUri,
            publicUri: $publicUri,
            outputPath: $outputPath,
            kind: 'artifact',
            routeName: $routeName,
        );
    }

    public static function redirect(
        string $sourceUri,
        string $publicUri,
        string $outputPath,
        string $redirectTo,
    ): self {
        return new self(
            sourceUri: $sourceUri,
            publicUri: $publicUri,
            outputPath: $outputPath,
            kind: 'redirect',
            redirectTo: $redirectTo,
        );
    }
}
