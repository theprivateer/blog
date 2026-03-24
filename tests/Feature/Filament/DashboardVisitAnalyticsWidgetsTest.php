<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
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
            ->assertSee('Total visits');

        Livewire::test(TopVisitedPaths::class)
            ->assertSee('Top visited pages')
            ->assertSee('/blog');
    }
}
