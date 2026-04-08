<?php

namespace Privateer\Basecms\Services;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Privateer\Basecms\Contracts\ResolvesCurrentSite;
use Privateer\Basecms\Models\Site;

class SiteManager
{
    private ?Site $forcedSite = null;

    public function __construct(private readonly ResolvesCurrentSite $resolver) {}

    public function enabled(): bool
    {
        return (bool) config('basecms.multisite.enabled', false);
    }

    public function current(): ?Site
    {
        if ($this->forcedSite instanceof Site) {
            return $this->forcedSite;
        }

        $tenant = Filament::getTenant();

        if ($tenant instanceof Site) {
            return $tenant;
        }

        if (! $this->enabled()) {
            return $this->default();
        }

        return $this->resolver->resolve();
    }

    public function siteForRequest(): Site
    {
        $site = $this->current();

        if ($site instanceof Site) {
            return $site;
        }

        abort(404);
    }

    public function default(): Site
    {
        $siteModel = (string) config('basecms.models.site', Site::class);

        /** @var Site|null $site */
        $site = $siteModel::query()->orderBy('id')->first();

        if ($site instanceof Site) {
            return $site;
        }

        /** @var Site $site */
        $site = $siteModel::query()->create([
            'name' => 'Default Site',
            'key' => 'default',
        ]);

        return $site;
    }

    public function required(): Site
    {
        return $this->current() ?? $this->default();
    }

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function runFor(Site $site, Closure $callback): mixed
    {
        $originalForcedSite = $this->forcedSite;

        $this->forcedSite = $site;

        try {
            return $callback();
        } finally {
            $this->forcedSite = $originalForcedSite;
        }
    }

    public function makeSiteNameFromKey(string $key): string
    {
        return Str::headline(str_replace(['_', '-'], ' ', $key));
    }
}
