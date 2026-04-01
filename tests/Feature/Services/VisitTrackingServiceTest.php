<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Privateer\Basecms\Services\VisitClassifier;
use Privateer\Basecms\Services\VisitTrackingService;
use Tests\TestCase;

class VisitTrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_visit_creates_visit_record(): void
    {
        $service = app(VisitTrackingService::class);

        $request = Request::create('/blog', 'GET');
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request, 200);

        $this->assertDatabaseHas('visits', [
            'path' => 'blog',
            'method' => 'GET',
            'response_status' => 200,
        ]);
    }

    public function test_track_visit_stores_correct_fields(): void
    {
        $service = app(VisitTrackingService::class);

        $request = Request::create('/notes', 'GET', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_USER_AGENT' => 'TestBrowser/1.0',
        ]);
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request, 200);

        $this->assertDatabaseHas('visits', [
            'path' => 'notes',
            'method' => 'GET',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'TestBrowser/1.0',
            'response_status' => 200,
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'classification_source' => VisitClassifier::SOURCE_FALLBACK,
        ]);
    }

    public function test_track_visit_truncates_long_user_agent(): void
    {
        $service = app(VisitTrackingService::class);

        $longUserAgent = str_repeat('A', 300);
        $request = Request::create('/', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => $longUserAgent,
        ]);
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request, 200);

        $this->assertDatabaseCount('visits', 1);
    }

    public function test_track_visit_skips_livewire_requests(): void
    {
        $service = app(VisitTrackingService::class);

        $request = Request::create('/livewire/update', 'POST');
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request, 200);

        $this->assertDatabaseCount('visits', 0);
    }

    public function test_track_visit_stores_non_success_response_status(): void
    {
        $service = app(VisitTrackingService::class);

        $request = Request::create('/missing-page', 'GET');
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request, 404);

        $this->assertDatabaseHas('visits', [
            'path' => 'missing-page',
            'response_status' => 404,
        ]);
    }

    public function test_track_visit_falls_back_to_unknown_when_classifier_fails(): void
    {
        $classifier = Mockery::mock(VisitClassifier::class);
        $classifier
            ->shouldReceive('classify')
            ->once()
            ->andReturn([
                'visitor_type' => VisitClassifier::TYPE_UNKNOWN,
                'visitor_label' => null,
                'classification_source' => VisitClassifier::SOURCE_FALLBACK,
            ]);

        $service = new VisitTrackingService($classifier);

        $request = Request::create('/blog', 'GET', [], [], [], [
            'HTTP_USER_AGENT' => 'BrokenAgent/1.0',
        ]);
        $request->setLaravelSession(app('session.store'));

        $service->trackVisit($request, 500);

        $this->assertDatabaseHas('visits', [
            'path' => 'blog',
            'response_status' => 500,
            'visitor_type' => VisitClassifier::TYPE_UNKNOWN,
            'classification_source' => VisitClassifier::SOURCE_FALLBACK,
        ]);
    }
}
