<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Pages\Dashboard;
use Privateer\Basecms\Filament\Widgets\TopVisitedPaths;
use Privateer\Basecms\Filament\Widgets\VisitAnalyticsOverview;
use Privateer\Basecms\Filament\Widgets\VisitClassificationBreakdown;
use Tests\TestCase;

class DashboardAnalyticsVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        config()->set('basecms.multisite.enabled', true);
        $this->actingOnTenant($this->makeSite());
    }

    public function test_analytics_widgets_and_navigation_are_hidden_when_visit_tracking_is_disabled(): void
    {
        config()->set('basecms.visits.track_visits', false);

        $this->assertFalse(VisitAnalyticsOverview::canView());
        $this->assertFalse(TopVisitedPaths::canView());
        $this->assertFalse(VisitClassificationBreakdown::canView());

        $this->assertSame('Dashboard', Dashboard::getNavigationLabel());
        $this->assertSame('Dashboard', (string) (new Dashboard)->getTitle());

        $this->actingAs(User::factory()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertDontSee('Analytics')
            ->assertDontSee('Visit window');

        Livewire::test(Dashboard::class)
            ->assertDontSee('Visit window')
            ->assertDontSee('Response status');
    }

    public function test_analytics_widgets_and_navigation_are_visible_when_visit_tracking_is_enabled(): void
    {
        config()->set('basecms.visits.track_visits', true);

        $this->assertTrue(VisitAnalyticsOverview::canView());
        $this->assertTrue(TopVisitedPaths::canView());
        $this->assertTrue(VisitClassificationBreakdown::canView());

        $this->assertSame('Analytics', Dashboard::getNavigationLabel());
        $this->assertSame('Analytics', (string) (new Dashboard)->getTitle());
    }
}
