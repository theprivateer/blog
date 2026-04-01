<?php

namespace Privateer\Basecms\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Privateer\Basecms\Models\Visit;

class VisitTrackingService
{
    public function __construct(private readonly VisitClassifier $visitClassifier) {}

    public function trackVisit(Request $request, int $responseStatus): void
    {
        if (! $this->shouldTrack($request)) {
            return;
        }

        $userAgent = Str::of($request->userAgent())->limit(250)->value();
        $classification = $this->visitClassifier->classify($userAgent);

        Visit::create([
            'path' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'session_id' => session()->id(),
            'user_agent' => $userAgent,
            'response_status' => $responseStatus,
            ...$classification,
        ]);
    }

    protected function shouldTrack(Request $request): bool
    {
        return ! Str::startsWith($request->path(), ['livewire-', 'livewire/']);
    }
}
