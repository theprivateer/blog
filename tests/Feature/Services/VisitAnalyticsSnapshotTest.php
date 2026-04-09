<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\VisitAnalyticsSnapshot;
use Privateer\Basecms\Services\VisitClassifier;
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
        $this->assertSame('Past 7 days', $totals['window_label']);
        $this->assertSame('Rolling 7-day average', $totals['average_label']);
    }

    public function test_totals_can_use_a_three_day_window(): void
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
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $totals = app(VisitAnalyticsSnapshot::class)->totals([
            'window' => VisitAnalyticsSnapshot::WINDOW_THREE_DAYS,
        ]);

        $this->assertSame(1, $totals['total_visits']);
        $this->assertSame(1, $totals['unique_visits']);
        $this->assertSame(1, $totals['average_daily_visits']);
        $this->assertSame('Past 3 days', $totals['window_label']);
        $this->assertSame('Rolling 3-day average', $totals['average_label']);
    }

    public function test_totals_can_use_a_twenty_four_hour_window(): void
    {
        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subHours(23),
            'updated_at' => now()->subHours(23),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'created_at' => now()->subHours(25),
            'updated_at' => now()->subHours(25),
        ]);

        $totals = app(VisitAnalyticsSnapshot::class)->totals([
            'window' => VisitAnalyticsSnapshot::WINDOW_TWENTY_FOUR_HOURS,
        ]);

        $this->assertSame(1, $totals['total_visits']);
        $this->assertSame(1, $totals['unique_visits']);
        $this->assertSame(1, $totals['average_daily_visits']);
        $this->assertSame('Past 24 hours', $totals['window_label']);
        $this->assertSame('24-hour average', $totals['average_label']);
    }

    public function test_custom_date_range_uses_inclusive_days(): void
    {
        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subDays(3)->startOfDay(),
            'updated_at' => now()->subDays(3)->startOfDay(),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'session_id' => 'session-2',
            'created_at' => now()->subDay()->endOfDay(),
            'updated_at' => now()->subDay()->endOfDay(),
        ]);

        $totals = app(VisitAnalyticsSnapshot::class)->totals([
            'window' => VisitAnalyticsSnapshot::WINDOW_CUSTOM,
            'start_date' => now()->subDays(3)->toDateString(),
            'end_date' => now()->subDay()->toDateString(),
        ]);

        $this->assertSame(2, $totals['total_visits']);
        $this->assertSame(2, $totals['unique_visits']);
        $this->assertSame(1, $totals['average_daily_visits']);
        $this->assertSame('Average per day (3-day range)', $totals['average_label']);
    }

    public function test_custom_date_range_normalizes_reversed_dates(): void
    {
        Visit::factory()->create([
            'path' => 'blog',
            'session_id' => 'session-1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $totals = app(VisitAnalyticsSnapshot::class)->totals([
            'window' => VisitAnalyticsSnapshot::WINDOW_CUSTOM,
            'start_date' => now()->toDateString(),
            'end_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->assertSame(1, $totals['total_visits']);
    }

    public function test_custom_date_range_falls_back_to_default_when_dates_are_missing(): void
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
            'created_at' => now()->subDays(6),
            'updated_at' => now()->subDays(6),
        ]);

        $totals = app(VisitAnalyticsSnapshot::class)->totals([
            'window' => VisitAnalyticsSnapshot::WINDOW_CUSTOM,
            'start_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->assertSame(2, $totals['total_visits']);
        $this->assertSame('Past 7 days', $totals['window_label']);
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

    public function test_classification_breakdown_returns_percentages_for_each_type(): void
    {
        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_AI_CRAWLER,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_AI_CRAWLER,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_UNKNOWN,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $breakdown = app(VisitAnalyticsSnapshot::class)->classificationBreakdown();

        $this->assertSame('Likely human', $breakdown[0]['label']);
        $this->assertSame(1, $breakdown[0]['count']);
        $this->assertSame('25.0%', $breakdown[0]['formatted_percentage']);
        $this->assertSame('AI crawler', $breakdown[1]['label']);
        $this->assertSame(2, $breakdown[1]['count']);
        $this->assertSame('50.0%', $breakdown[1]['formatted_percentage']);
        $this->assertSame('Unknown', $breakdown[4]['label']);
        $this->assertSame(1, $breakdown[4]['count']);
        $this->assertSame('25.0%', $breakdown[4]['formatted_percentage']);
    }

    public function test_classification_breakdown_respects_selected_window(): void
    {
        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'response_status' => 200,
            'created_at' => now()->subHours(20),
            'updated_at' => now()->subHours(20),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_OTHER_BOT,
            'response_status' => 404,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $breakdown = app(VisitAnalyticsSnapshot::class)->classificationBreakdown([
            'window' => VisitAnalyticsSnapshot::WINDOW_TWENTY_FOUR_HOURS,
        ]);

        $this->assertSame(1, $breakdown[0]['count']);
        $this->assertSame('100.0%', $breakdown[0]['formatted_percentage']);
        $this->assertSame(0, $breakdown[3]['count']);
    }

    public function test_totals_respect_selected_response_status_and_all_include_all_recorded_statuses(): void
    {
        Visit::factory()->create([
            'response_status' => 200,
            'session_id' => 'session-1',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'response_status' => 404,
            'session_id' => 'session-2',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'response_status' => 200,
            'session_id' => 'session-3',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $allTotals = app(VisitAnalyticsSnapshot::class)->totals([
            'response_status' => VisitAnalyticsSnapshot::DEFAULT_RESPONSE_STATUS,
        ]);

        $filteredTotals = app(VisitAnalyticsSnapshot::class)->totals([
            'response_status' => '404',
        ]);

        $this->assertSame(3, $allTotals['total_visits']);
        $this->assertSame(1, $filteredTotals['total_visits']);
        $this->assertSame(1, $filteredTotals['unique_visits']);
    }

    public function test_top_paths_respect_selected_response_status(): void
    {
        Visit::factory()->create([
            'path' => 'blog',
            'response_status' => 404,
            'session_id' => 'session-1',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'path' => 'notes',
            'response_status' => 200,
            'session_id' => 'session-2',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $topPaths = app(VisitAnalyticsSnapshot::class)->topPaths(10, [
            'response_status' => '404',
        ]);

        $this->assertCount(1, $topPaths);
        $this->assertSame('blog', $topPaths[0]->path);
    }

    public function test_classification_breakdown_respects_selected_response_status(): void
    {
        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_LIKELY_HUMAN,
            'response_status' => 200,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Visit::factory()->create([
            'visitor_type' => VisitClassifier::TYPE_OTHER_BOT,
            'response_status' => 404,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $breakdown = app(VisitAnalyticsSnapshot::class)->classificationBreakdown([
            'response_status' => '404',
        ]);

        $this->assertSame(0, $breakdown[0]['count']);
        $this->assertSame(1, $breakdown[3]['count']);
        $this->assertSame('100.0%', $breakdown[3]['formatted_percentage']);
    }
}
