<?php

namespace Privateer\Basecms\Models\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\SiteManager;

trait BelongsToSite
{
    public static function bootBelongsToSite(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('site_id')) {
                return;
            }

            $tenant = Filament::getTenant();

            if ($tenant instanceof Site) {
                $model->site()->associate($tenant);

                return;
            }

            $model->site()->associate(app(SiteManager::class)->required());
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo((string) config('basecms.models.site', Site::class));
    }

    public function scopeForSite(Builder $query, Site $site): Builder
    {
        return $query->where($query->qualifyColumn('site_id'), $site->getKey());
    }
}
