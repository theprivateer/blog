<?php

namespace Tests\Feature\Commands;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GenerateSitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    protected function tearDown(): void
    {
        $sitemapPath = public_path('sitemap.xml');
        if (file_exists($sitemapPath)) {
            unlink($sitemapPath);
        }

        parent::tearDown();
    }

    public function test_generate_sitemap_command_runs_successfully(): void
    {
        Page::factory()->homepage()->create();
        Post::factory()->published()->create();

        $this->artisan('app:generate-sitemap')
            ->assertSuccessful();
    }

    public function test_generate_sitemap_creates_sitemap_file(): void
    {
        Page::factory()->homepage()->create();
        Post::factory()->published()->create();

        $this->artisan('app:generate-sitemap');

        $this->assertFileExists(public_path('sitemap.xml'));
    }
}
