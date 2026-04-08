<?php

namespace Privateer\Basecms\Services;

use Privateer\Basecms\Contracts\ResolvesCurrentSite;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Site;

class DomainCurrentSiteResolver implements ResolvesCurrentSite
{
    public function resolve(): ?Site
    {
        $host = request()->getHost();

        if ($host === '') {
            return null;
        }

        $domainModel = (string) config('basecms.models.domain', Domain::class);

        /** @var Domain|null $domain */
        $domain = $domainModel::query()
            ->with('site.domains')
            ->where('domain', $host)
            ->first();

        return $domain?->site;
    }
}
