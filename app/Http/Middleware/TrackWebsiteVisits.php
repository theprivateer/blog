<?php

namespace App\Http\Middleware;

use App\Services\VisitTrackingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('tracking.track_visits') && ! $request->user()) {
            (new VisitTrackingService)->trackVisit($request);
        }

        return $next($request);
    }
}
