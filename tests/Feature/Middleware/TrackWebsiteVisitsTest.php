<?php

namespace Tests\Feature\Middleware;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TrackWebsiteVisitsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        Page::factory()->homepage()->create();
    }

    public function test_visit_is_tracked_when_config_enabled(): void
    {
        config(['tracking.track_visits' => true]);

        $this->get('/');

        $this->assertDatabaseCount('visits', 1);
    }

    public function test_visit_is_not_tracked_when_config_disabled(): void
    {
        config(['tracking.track_visits' => false]);

        $this->get('/');

        $this->assertDatabaseCount('visits', 0);
    }

    public function test_visit_is_not_tracked_for_authenticated_user(): void
    {
        config(['tracking.track_visits' => true]);

        $this->actingAs(User::factory()->create());

        $this->get('/');

        $this->assertDatabaseCount('visits', 0);
    }

    public function test_request_still_processes_when_tracking_disabled(): void
    {
        config(['tracking.track_visits' => false]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_request_still_processes_when_tracking_enabled(): void
    {
        config(['tracking.track_visits' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_livewire_requests_are_not_tracked(): void
    {
        config(['tracking.track_visits' => true]);

        $response = $this->post('/livewire/update');

        $response->assertNotFound();
        $this->assertDatabaseCount('visits', 0);
    }
}
