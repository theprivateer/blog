<?php

namespace Tests\Feature\Seeders;

use App\Models\Note;
use App\Models\Page;
use App\Models\Post;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Tests\TestCase;

class VisitSeederTest extends TestCase
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

    public function test_visit_seeder_creates_realistic_visit_data_for_public_paths(): void
    {
        Page::factory()->createQuietly([
            'title' => 'About',
            'slug' => 'about',
            'draft' => false,
        ]);

        Page::factory()->createQuietly([
            'title' => 'Hidden',
            'slug' => 'hidden',
            'draft' => true,
        ]);

        Post::factory()->published()->createQuietly([
            'title' => 'Launch Post',
            'slug' => 'launch-post',
        ]);

        Note::factory()->createQuietly([
            'title' => 'Quick Note',
            'slug' => 'quick-note',
        ]);

        $this->artisan('db:seed', [
            '--class' => 'VisitSeeder',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $visitCount = Visit::query()->count();

        $this->assertGreaterThanOrEqual(200, $visitCount);
        $this->assertLessThanOrEqual(300, $visitCount);

        $this->assertTrue(
            Visit::query()
                ->where('created_at', '<', now()->subDays(7))
                ->doesntExist()
        );

        $this->assertTrue(
            Visit::query()
                ->where('created_at', '>', now())
                ->doesntExist()
        );

        $expectedPaths = [
            '/',
            'blog',
            'notes',
            'about',
            'blog/launch-post',
            'notes/quick-note',
        ];

        $this->assertEmpty(
            Visit::query()
                ->whereNotIn('path', $expectedPaths)
                ->pluck('path')
                ->all()
        );

        $this->assertTrue(
            Visit::query()
                ->where('path', 'like', 'livewire-%')
                ->doesntExist()
        );

        $this->assertTrue(
            Visit::query()
                ->select('session_id')
                ->groupBy('session_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists()
        );
    }

    public function test_visit_seeder_skips_when_no_content_paths_exist(): void
    {
        $this->artisan('db:seed', [
            '--class' => 'VisitSeeder',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertDatabaseCount('visits', 0);
    }
}
