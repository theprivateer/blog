<?php

namespace Privateer\Basecms\Mcp\Support;

use Privateer\Basecms\Models\Site;

class McpAccess
{
    /**
     * @param  array<int, string>  $abilities
     */
    public function __construct(
        private readonly array $abilities,
        private readonly ?Site $site = null,
    ) {}

    /**
     * Trusted default for contexts with no bound access (e.g. the local stdio server).
     */
    public static function fullAccess(): self
    {
        return new self(['*']);
    }

    public static function current(): self
    {
        return app()->bound(self::class) ? app(self::class) : self::fullAccess();
    }

    public function can(string $ability): bool
    {
        return in_array('*', $this->abilities, true) || in_array($ability, $this->abilities, true);
    }

    /**
     * @param  array<int, string>  $abilities
     */
    public function canAny(array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->can($ability)) {
                return true;
            }
        }

        return false;
    }

    public function site(): ?Site
    {
        return $this->site;
    }
}
