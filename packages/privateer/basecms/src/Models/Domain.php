<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Privateer\Basecms\Database\Factories\DomainFactory;
use Privateer\Basecms\Models\Concerns\BelongsToSite;

class Domain extends Model
{
    use BelongsToSite;

    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    protected $fillable = [
        'site_id',
        'domain',
        'is_primary',
    ];

    protected static function newFactory(): DomainFactory
    {
        return DomainFactory::new();
    }

    protected static function booted(): void
    {
        // After any save or delete, re-evaluate which domain should be primary for the site.
        // On save we pass the saved domain as the preferred candidate; on delete we pass none
        // so the normalizer falls back to whichever domain already carries is_primary=true.
        static::saved(function (Domain $domain): void {
            $siteId = $domain->getAttribute('site_id');

            if (! is_int($siteId)) {
                return;
            }

            static::normalizePrimaryDomainForSite(
                siteId: $siteId,
                preferredDomainId: $domain->resolvePreferredPrimaryDomainId(),
            );
        });

        static::deleted(function (Domain $domain): void {
            $siteId = $domain->getAttribute('site_id');

            if (! is_int($siteId)) {
                return;
            }

            static::normalizePrimaryDomainForSite(siteId: $siteId);
        });
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    protected function resolvePreferredPrimaryDomainId(): ?int
    {
        if (! $this->exists) {
            return null;
        }

        if ($this->is_primary) {
            return $this->getKey();
        }

        // When is_primary is explicitly set to false on an existing domain (not newly created),
        // still treat it as the preferred candidate so normalizePrimaryDomainForSite can
        // re-evaluate rather than leaving the site with no primary domain.
        if (! $this->wasRecentlyCreated && $this->wasChanged('is_primary') && $this->getOriginal('is_primary')) {
            return $this->getKey();
        }

        return null;
    }

    protected static function normalizePrimaryDomainForSite(int $siteId, ?int $preferredDomainId = null): void
    {
        /** @var Collection<int, Domain> $domains */
        $domains = static::query()
            ->where('site_id', $siteId)
            ->orderByDesc('is_primary')
            ->orderBy('id')
            ->get(['id', 'is_primary']);

        if ($domains->isEmpty()) {
            return;
        }

        // Three-tier selection: prefer the explicitly nominated domain → fall back to whichever
        // domain already has is_primary=true → last resort: the lowest-id domain.
        $primaryDomainId = $domains
            ->firstWhere('id', $preferredDomainId)
            ?->getKey();

        if ($primaryDomainId === null) {
            $primaryDomainId = $domains
                ->firstWhere('is_primary', true)
                ?->getKey();
        }

        $primaryDomainId ??= $domains->first()->getKey();

        // Two targeted queries: strip is_primary from every other domain, then set it on the winner.
        // The where('is_primary', ...) guards mean neither query fires when the state is already correct.
        static::query()
            ->where('site_id', $siteId)
            ->whereKeyNot($primaryDomainId)
            ->where('is_primary', true)
            ->update(['is_primary' => false]);

        static::query()
            ->whereKey($primaryDomainId)
            ->where('is_primary', false)
            ->update(['is_primary' => true]);
    }
}
