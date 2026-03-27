<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Pages\Dashboard;
use Privateer\Basecms\Filament\Widgets\TopVisitedPaths;
use Privateer\Basecms\Filament\Widgets\VisitAnalyticsOverview;
use Privateer\Basecms\Models\Visit;
use Tests\TestCase;

class DashboardVisitAnalyticsWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        Carbon::setTestNow('2026-03-24 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_authenticated_user_can_view_dashboard_visit_analytics_widgets(): void
    {
        $this->actingAs(User::factory()->create());

        Visit::factory()->count(3)->create([
            'path' => 'blog',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->get('/admin')
            ->assertOk();

        Livewire::test(VisitAnalyticsOverview::class)
            ->assertSee('Visit analytics')
            ->assertSee('Total visits')
            ->assertSee('Past 7 days');

        Livewire::test(TopVisitedPaths::class)
            ->assertSee('Top visited pages')
            ->assertSee('/blog');
    }

    public function test_dashboard_filters_show_custom_date_inputs_only_for_custom_window(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Dashboard::class)
            ->assertFormFieldIsHidden('start_date', 'filtersForm')
            ->assertFormFieldIsHidden('end_date', 'filtersForm')
            ->fillForm([
                'window' => 'custom',
            ], 'filtersForm')
            ->assertFormFieldIsVisible('start_date', 'filtersForm')
            ->assertFormFieldIsVisible('end_date', 'filtersForm');
    }

    public function test_visit_analytics_overview_responds_to_shared_dashboard_filters(): void
    {
        $this->actingAs(User::factory()->create());

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Visit::factory()->create([
            'path' => 'projects',
            'session_id' => 'session-3',
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        Livewire::test(VisitAnalyticsOverview::class, [
            'pageFilters' => [
                'window' => '3_days',
            ],
        ])
            ->assertSee('2')
            ->assertSee('Past 3 days')
            ->assertSee('Rolling 3-day average');

        Livewire::test(VisitAnalyticsOverview::class, [
            'pageFilters' => [
                'window' => '24_hours',
            ],
        ])
            ->assertSee('1')
            ->assertSee('Past 24 hours')
            ->assertSee('24-hour average');

        Livewire::test(VisitAnalyticsOverview::class, [
            'pageFilters' => [
                'window' => 'custom',
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->subDay()->toDateString(),
            ],
        ])
            ->assertSee('1')
            ->assertSee('Average per day (2-day range)');
    }

    public function test_top_visited_paths_widget_responds_to_shared_dashboard_filters(): void
    {
        $this->actingAs(User::factory()->create());

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Visit::factory()->create([
            'path' => 'projects',
            'session_id' => 'session-3',
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        Livewire::test(TopVisitedPaths::class, [
            'pageFilters' => [
                'window' => '3_days',
            ],
        ])
            ->assertSee('/blog')
            ->assertSee('/notes')
            ->assertDontSee('/projects');

        Livewire::test(TopVisitedPaths::class, [
            'pageFilters' => [
                'window' => '24_hours',
            ],
        ])
            ->assertSee('/blog')
            ->assertDontSee('/notes')
            ->assertDontSee('/projects');
    }
}
