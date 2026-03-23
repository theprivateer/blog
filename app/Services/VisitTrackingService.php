<?php

namespace App\Services;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitTrackingService
{
    public function trackVisit(Request $request): void
    {
        if (! $this->shouldTrack($request)) {
            return;
        }

        Visit::create([
            'path' => $request->path(),
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'session_id' => session()->id(),
            'user_agent' => Str::of($request->userAgent())->limit(250),
        ]);
    }

    protected function shouldTrack(Request $request): bool
    {
        return ! Str::startsWith($request->path(), ['livewire-', 'livewire/']);
    }
}
