<?php

namespace Privateer\Basecms\Console\Commands\Concerns;

use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;

use function Laravel\Prompts\select;

trait InteractsWithSelectedSite
{
    protected function resolveSelectedSite(): ?Site
    {
        /** @var SiteManager $siteManager */
        $siteManager = app(SiteManager::class);

        if (! $siteManager->enabled()) {
            return $siteManager->default();
        }

        $selectedSiteKey = $this->option('site');

        if (is_string($selectedSiteKey) && $selectedSiteKey !== '') {
            return $this->resolveSiteByKey($selectedSiteKey);
        }

        if (! $this->input->isInteractive()) {
            $this->error('Multi-site mode requires a target site. Re-run this command with --site=<site-key>.');

            return null;
        }

        $siteModelClass = (string) config('basecms.models.site', Site::class);

        /** @var Site|null $firstSite */
        $firstSite = $siteModelClass::query()->orderBy('name')->orderBy('id')->first();

        if (! $firstSite instanceof Site) {
            $this->error('No sites are available to select.');

            return null;
        }

        /** @var array<string, string> $options */
        $options = $siteModelClass::query()
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (Site $site): array => [
                $site->key => "{$site->name} ({$site->key})",
            ])
            ->all();

        $selectedSiteKey = select(
            label: 'Which site should this command run for?',
            options: $options,
            default: $firstSite->key,
        );

        return $this->resolveSiteByKey($selectedSiteKey);
    }

    protected function describeSelectedSite(Site $site): string
    {
        return "{$site->name} ({$site->key})";
    }

    protected function resolveSiteByKey(string $siteKey): ?Site
    {
        $siteModelClass = (string) config('basecms.models.site', Site::class);

        /** @var Site|null $site */
        $site = $siteModelClass::query()
            ->where('key', $siteKey)
            ->first();

        if ($site instanceof Site) {
            return $site;
        }

        $this->error("Unknown site [{$siteKey}].");

        return null;
    }
}
