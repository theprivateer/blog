<?php

namespace Tests\Feature\Feature\Filament;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Filament\Widgets\TopVisitedPaths;
use App\Filament\Widgets\VisitAnalyticsOverview;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
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
