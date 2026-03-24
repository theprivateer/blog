<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
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
