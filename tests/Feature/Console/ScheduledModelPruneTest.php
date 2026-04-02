<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduledModelPruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_prune_is_scheduled_daily_for_app_visit_model(): void
    {
        $events = app(Schedule::class)->events();

        $pruneEvent = collect($events)
            ->first(fn ($event): bool => str_contains($event->command, 'model:prune')
                && str_contains($event->command, 'App\\Models\\Visit'));

        $this->assertNotNull($pruneEvent);
        $this->assertSame('0 0 * * *', $pruneEvent->expression);
    }
}
