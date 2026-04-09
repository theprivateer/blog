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

        $primaryDomainId = $domains
            ->firstWhere('id', $preferredDomainId)
            ?->getKey();

        if ($primaryDomainId === null) {
            $primaryDomainId = $domains
                ->firstWhere('is_primary', true)
                ?->getKey();
        }

        $primaryDomainId ??= $domains->first()->getKey();

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
