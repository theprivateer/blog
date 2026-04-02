<?php

namespace Tests\Feature\Models;

use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AppVisitTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_prunable_query_targets_visits_older_than_thirty_days(): void
    {
        Carbon::setTestNow('2026-04-03 10:00:00');

        $staleVisit = Visit::factory()->create([
            'created_at' => now()->subDays(31),
            'updated_at' => now()->subDays(31),
        ]);

        $recentVisit = Visit::factory()->create([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        $prunableIds = (new Visit)->prunable()->pluck('id')->all();

        $this->assertContains($staleVisit->id, $prunableIds);
        $this->assertNotContains($recentVisit->id, $prunableIds);
    }
}
