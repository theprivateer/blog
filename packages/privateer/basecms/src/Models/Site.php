<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Privateer\Basecms\Database\Factories\SiteFactory;

class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
    ];

    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function domains(): HasMany
    {
        return $this->hasMany((string) config('basecms.models.domain', Domain::class));
    }

    public function primaryDomain(): ?Domain
    {
        if ($this->relationLoaded('domains')) {
            return $this->domains->firstWhere('is_primary', true)
                ?? $this->domains->first();
        }

        return $this->domains()
            ->orderByDesc('is_primary')
            ->orderBy('domain')
            ->first();
    }

    public function primaryUrl(?string $fallbackBaseUrl = null): ?string
    {
        $domain = $this->primaryDomain();

        if (! $domain instanceof Domain) {
            return null;
        }

        $baseUrl = $fallbackBaseUrl ?: (string) config('basecms.static_site.base_url', (string) config('app.url'));
        $scheme = parse_url($baseUrl, PHP_URL_SCHEME);
        $scheme = is_string($scheme) && $scheme !== '' ? $scheme : 'https';

        return sprintf('%s://%s', $scheme, $domain->domain);
    }

    public function posts(): HasMany
    {
        return $this->hasMany((string) config('basecms.models.post', Post::class));
    }

    public function pages(): HasMany
    {
        return $this->hasMany((string) config('basecms.models.page', Page::class));
    }

    public function categories(): HasMany
    {
        return $this->hasMany((string) config('basecms.models.category', Category::class));
    }

    public function assets(): HasMany
    {
        return $this->hasMany((string) config('basecms.models.asset', Asset::class));
    }

    public function visits(): HasMany
    {
        return $this->hasMany((string) config('basecms.models.visit', Visit::class));
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
