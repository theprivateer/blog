<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;
use Tests\TestCase;

class VisitAnalyticsSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-24 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_totals_only_include_visits_from_the_past_seven_days(): void
    {
        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-3',
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        $totals = app(VisitAnalyticsSnapshot::class)->totals();

        $this->assertSame(2, $totals['total_visits']);
        $this->assertSame(2, $totals['unique_visits']);
        $this->assertSame(1, $totals['average_daily_visits']);
    }

    public function test_top_paths_group_by_path_and_count_unique_sessions(): void
    {
        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ]);

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-2',
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(6),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-3',
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-4',
            'created_at' => now()->subHours(4),
            'updated_at' => now()->subHours(4),
        ]);

        $topPaths = app(VisitAnalyticsSnapshot::class)->topPaths();

        $this->assertCount(2, $topPaths);
        $this->assertSame('blog', $topPaths[0]->path);
        $this->assertSame(3, $topPaths[0]->visit_count);
        $this->assertSame(2, $topPaths[0]->unique_visit_count);
        $this->assertSame('notes', $topPaths[1]->path);
        $this->assertSame(2, $topPaths[1]->visit_count);
        $this->assertSame(2, $topPaths[1]->unique_visit_count);
    }

    public function test_top_paths_are_ordered_by_visits_then_unique_visits(): void
    {
        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-1',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-3',
            'created_at' => now()->subHours(18),
            'updated_at' => now()->subHours(18),
        ]);

        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-3',
            'created_at' => now()->subHours(16),
            'updated_at' => now()->subHours(16),
        ]);

        $topPaths = app(VisitAnalyticsSnapshot::class)->topPaths();

        $this->assertSame('notes', $topPaths[0]->path);
        $this->assertSame('blog', $topPaths[1]->path);
    }

    public function test_format_path_returns_display_ready_paths(): void
    {
        $snapshot = app(VisitAnalyticsSnapshot::class);

        $this->assertSame('/', $snapshot->formatPath('/'));
        $this->assertSame('/blog', $snapshot->formatPath('blog'));
        $this->assertSame('/notes/example-note', $snapshot->formatPath('notes/example-note'));
    }
}
