<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Site;
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

        $this->artisan('basecms:generate-sitemap')
            ->assertSuccessful();
    }

    public function test_generate_sitemap_prompts_for_a_site_when_multisite_is_enabled(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $selectedSite = Site::factory()->create([
            'name' => 'Alpha Site',
            'key' => 'alpha',
        ]);
        Site::factory()->create([
            'name' => 'Beta Site',
            'key' => 'beta',
        ]);

        Page::factory()->homepage()->create(['site_id' => $selectedSite->id]);
        Post::factory()->published()->create(['site_id' => $selectedSite->id]);

        $this->artisan('basecms:generate-sitemap')
            ->expectsQuestion('Which site should this command run for?', 'alpha')
            ->expectsOutputToContain('Selected site: Alpha Site (alpha)')
            ->assertSuccessful();
    }

    public function test_generate_sitemap_fails_in_non_interactive_multisite_mode_without_a_site_option(): void
    {
        config()->set('basecms.multisite.enabled', true);
        Site::factory()->create([
            'name' => 'Alpha Site',
            'key' => 'alpha',
        ]);

        $this->withoutMockingConsoleOutput();

        $this->artisan('basecms:generate-sitemap', ['--no-interaction' => true]);

        $this->assertSame(1, $this->artisan('basecms:generate-sitemap', ['--no-interaction' => true]));
    }

    public function test_generate_sitemap_creates_sitemap_file(): void
    {
        Page::factory()->homepage()->create();
        Post::factory()->published()->create();

        $this->artisan('basecms:generate-sitemap');

        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_generate_sitemap_command_gracefully_skips_when_no_service_is_configured(): void
    {
        config()->set('basecms.services.sitemap', null);

        $this->artisan('basecms:generate-sitemap')
            ->expectsOutput('No sitemap service is configured. Nothing to generate.')
            ->assertSuccessful();

        $this->assertFileDoesNotExist(public_path('sitemap.xml'));
    }
}
