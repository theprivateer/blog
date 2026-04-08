<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Notes\NoteResource;
use App\Models\User;
use Filament\Support\Icons\Heroicon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Pages\Dashboard;
use Privateer\Basecms\Filament\Resources\Categories\CategoryResource;
use Privateer\Basecms\Filament\Resources\Pages\PageResource;
use Privateer\Basecms\Filament\Resources\Posts\PostResource;
use Privateer\Basecms\Filament\Widgets\TopVisitedPaths;
use Privateer\Basecms\Filament\Widgets\VisitAnalyticsOverview;
use Privateer\Basecms\Filament\Widgets\VisitClassificationBreakdown;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;
use Privateer\Basecms\Services\VisitClassifier;
use Tests\TestCase;

class DashboardVisitAnalyticsWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        Carbon::setTestNow('2026-03-24 10:00:00');
        config()->set('basecms.multisite.enabled', true);
        $this->site = $this->actingOnTenant($this->makeSite());
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
            ->assertOk()
            ->assertSee('Analytics');

        Livewire::test(VisitAnalyticsOverview::class)
            ->assertSee('Visit analytics')
            ->assertSee('Total visits')
            ->assertSee('Past 7 days');

        Livewire::test(TopVisitedPaths::class)
            ->assertSee('Top visited pages')
            ->assertSee('/blog');

        Livewire::test(VisitClassificationBreakdown::class)
            ->assertSee('Visit classification')
            ->assertSee('Likely human');
    }

    public function test_dashboard_route_renders_and_top_paths_only_include_the_active_site(): void
    {
        $this->actingAs(User::factory()->create());

        $otherSite = Site::factory()->create([
            'key' => 'secondary',
            'name' => 'Secondary Site',
        ]);

        Visit::factory()->create([
            'site_id' => $this->site->id,
            'path' => 'alpha-path',
            'response_status' => 200,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        Visit::factory()->create([
            'site_id' => $otherSite->id,
            'path' => 'beta-path',
            'response_status' => 404,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Analytics');

        Livewire::test(TopVisitedPaths::class, [
            'pageFilters' => [
                'window' => VisitAnalyticsSnapshot::DEFAULT_WINDOW,
            ],
        ])
            ->assertSee('/alpha-path')
            ->assertDontSee('/beta-path');
    }

    public function test_dashboard_and_resources_expose_the_expected_navigation_metadata(): void
    {
        $this->assertSame('Analytics', Dashboard::getNavigationLabel());
        $this->assertSame('Analytics', (string) (new Dashboard)->getTitle());
        $this->assertSame(Heroicon::OutlinedChartBarSquare, Dashboard::getNavigationIcon());
        $this->assertSame(Heroicon::OutlinedNewspaper, PostResource::getNavigationIcon());
        $this->assertSame(Heroicon::OutlinedDocumentText, PageResource::getNavigationIcon());
        $this->assertSame(Heroicon::OutlinedTag, CategoryResource::getNavigationIcon());
        $this->assertSame(Heroicon::OutlinedPencilSquare, NoteResource::getNavigationIcon());
    }

    public function test_dashboard_filters_show_custom_date_inputs_only_for_custom_window(): void
    {
        $this->actingAs(User::factory()->create());

        Visit::factory()->create(['response_status' => 200]);
        Visit::factory()->create(['response_status' => 404]);

        Livewire::test(Dashboard::class)
            ->assertSee('Updating analytics...')
            ->assertSee('wire:target="filters"', escape: false)
            ->assertSee('Response status')
            ->assertSee('All')
            ->assertSee('200')
            ->assertSee('404')
            ->assertFormSet([
                'window' => VisitAnalyticsSnapshot::DEFAULT_WINDOW,
                'response_status' => VisitAnalyticsSnapshot::DEFAULT_RESPONSE_STATUS,
            ], 'filtersForm')
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
            'response_status' => 404,
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'response_status' => 200,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Visit::factory()->create([
            'path' => 'projects',
            'session_id' => 'session-3',
            'response_status' => null,
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
                'window' => '7_days',
                'response_status' => '404',
            ],
        ])
            ->assertSee('1')
            ->assertSee('Past 7 days');

        Livewire::test(VisitAnalyticsOverview::class, [
            'pageFilters' => [
                'window' => '7_days',
                'response_status' => VisitAnalyticsSnapshot::DEFAULT_RESPONSE_STATUS,
            ],
        ])
            ->assertSee('3');

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
            'response_status' => 404,
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'response_status' => 200,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Visit::factory()->create([
            'path' => 'projects',
            'session_id' => 'session-3',
            'response_status' => null,
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

        Livewire::test(TopVisitedPaths::class, [
            'pageFilters' => [
                'window' => '7_days',
                'response_status' => '404',
            ],
        ])
            ->assertSee('/blog')
            ->assertDontSee('/projects');
    }

    public function test_top_visited_paths_widget_disables_default_primary_key_sorting(): void
    {
        $this->actingAs(User::factory()->create());

        $component = Livewire::test(TopVisitedPaths::class);

        $this->assertFalse($component->instance()->getTable()->hasDefaultKeySort());
    }

    public function test_visit_classification_breakdown_responds_to_shared_dashboard_filters(): void
    {
        $this->actingAs(User::factory()->create());

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'response_status' => 404,
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_AI_CRAWLER,
            'response_status' => 200,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_SEARCH_CRAWLER,
            'response_status' => null,
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        Livewire::test(VisitClassificationBreakdown::class, [
            'pageFilters' => [
                'window' => '3_days',
            ],
        ])
            ->assertSee('Likely human')
            ->assertSee('AI crawler')
            ->assertSee('50.0%')
            ->assertSee('Search crawler')
            ->assertSee('0.0%')
            ->assertSee('Past 3 days');

        Livewire::test(VisitClassificationBreakdown::class, [
            'pageFilters' => [
                'window' => '24_hours',
            ],
        ])
            ->assertSee('Likely human')
            ->assertSee('100.0%')
            ->assertSee('AI crawler')
            ->assertSee('Past 24 hours');

        Livewire::test(VisitClassificationBreakdown::class, [
            'pageFilters' => [
                'window' => '7_days',
                'response_status' => '404',
            ],
        ])
            ->assertSee('Likely human')
            ->assertSee('100.0%')
            ->assertSee('AI crawler')
            ->assertSee('0.0%');
    }
}
