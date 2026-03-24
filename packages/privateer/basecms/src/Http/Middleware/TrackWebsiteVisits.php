<?php

namespace Privateer\Basecms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Privateer\Basecms\Services\VisitTrackingService;
use Symfony\Component\HttpFoundation\Response;

class TrackWebsiteVisits
{
    public function __construct(private VisitTrackingService $visitTrackingService) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('tracking.track_visits') && ! $request->user()) {
            $this->visitTrackingService->trackVisit($request);
        }

        return $next($request);
    }
}
