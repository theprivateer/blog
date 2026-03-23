<?php

namespace Tests\Feature\Services;

use App\Services\VisitTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class VisitTrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_visit_creates_visit_record(): void
    {
        $service = new VisitTrackingService;

        $request = Request::create('/blog', 'GET');
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request);

        $this->assertDatabaseHas('visits', [
            'path' => 'blog',
            'method' => 'GET',
        ]);
    }

    public function test_track_visit_stores_correct_fields(): void
    {
        $service = new VisitTrackingService;

        $request = Request::create('/notes', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_USER_AGENT' => 'TestBrowser/1.0',
        ]);
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request);

        $this->assertDatabaseHas('visits', [
            'path' => 'notes',
            'method' => 'GET',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'TestBrowser/1.0',
        ]);
    }

    public function test_track_visit_truncates_long_user_agent(): void
    {
        $service = new VisitTrackingService;

        $longUserAgent = str_repeat('A', 300);
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => $longUserAgent,
        ]);
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request);

        $this->assertDatabaseCount('visits', 1);
    }
}
