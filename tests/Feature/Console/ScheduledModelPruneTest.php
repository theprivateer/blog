<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ScheduledModelPruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_prune_is_scheduled_daily_for_app_visit_model(): void
    {
        Artisan::call('schedule:list', ['--json' => true]);

        $events = json_decode(Artisan::output(), true, flags: JSON_THROW_ON_ERROR);

        $pruneEvent = collect($events)
            ->first(fn (array $event): bool => str_contains($event['command'], 'model:prune')
                && str_contains($event['command'], 'App\\Models\\Visit'));

        $this->assertNotNull($pruneEvent);
        $this->assertSame('0 0 * * *', $pruneEvent['expression']);
    }
}
