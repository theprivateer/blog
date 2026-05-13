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
        return $next($request);
    }

    // Tracking runs in terminate() rather than handle() so the HTTP status code is known.
    // handle() fires before the response is generated; terminate() fires after it has been sent.
    public function terminate(Request $request, Response $response): void
    {
        if (! config('basecms.visits.track_visits')) {
            return;
        }

        if ($request->user()) {
            return;
        }

        $this->visitTrackingService->trackVisit($request, $response->getStatusCode());
    }
}
