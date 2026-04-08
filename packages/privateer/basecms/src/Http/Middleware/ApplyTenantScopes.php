<?php

namespace Privateer\Basecms\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Privateer\Basecms\Models\Asset;
use Privateer\Basecms\Models\Visit;

class ApplyTenantScopes
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            Visit::addGlobalScope('tenant', fn (Builder $query): Builder => $query->whereBelongsTo($tenant, 'site'));
            Asset::addGlobalScope('tenant', fn (Builder $query): Builder => $query->whereBelongsTo($tenant, 'site'));
        }

        return $next($request);
    }
}
