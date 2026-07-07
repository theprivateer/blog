<?php

namespace Privateer\Basecms\Mcp\Tools\Concerns;

use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;

trait ResolvesMcpSite
{
    /**
     * Resolves an explicit site key (multisite installs) or falls back to the current/default site.
     */
    protected function resolveSite(?string $siteKey): Site
    {
        $siteManager = app(SiteManager::class);

        if ($siteKey === null) {
            return $siteManager->required();
        }

        $siteModel = (string) config('basecms.models.site', Site::class);

        /** @var Site|null $site */
        $site = $siteModel::query()->where('key', $siteKey)->first();

        return $site ?? $siteManager->required();
    }
}
