<?php

namespace Tests\Feature\Commands;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Laravel\Boost\Middleware\InjectBoost;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\Fixtures\StaticSite\BrokenStaticRouteExporter;
use Tests\TestCase;

class GenerateStaticSiteTest extends TestCase
{
    use RefreshDatabase;

    private string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        $this->outputPath = storage_path('app/testing-static-site');

        config()->set('basecms.static_site.enabled', true);
        config()->set('basecms.static_site.output_path', $this->outputPath);
        config()->set('basecms.static_site.clean_output_before_build', true);
        config()->set('basecms.static_site.generate_sitemap', true);
        config()->set('basecms.static_site.generate_feeds', true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->outputPath)) {
            $this->deleteDirectory($this->outputPath);
        }

        $sitemapPath = public_path('sitemap.xml');
        if (file_exists($sitemapPath)) {
            unlink($sitemapPath);
        }

        parent::tearDown();
    }

    public function test_generate_static_site_command_exports_core_pages(): void
    {
        $category = Category::factory()->create(['title' => 'Laravel']);
        Page::factory()->homepage()->create(['title' => 'Home']);
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'About', 'slug' => 'about']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);

        $post = Post::factory()->published()->create([
            'title' => 'Static Export Post',
            'category_id' => $category->id,
        ]);

        Note::factory()->create(['title' => 'Static Note']);

        $this->artisan('basecms:generate-static')
            ->expectsOutputToContain('Generating static site...')
            ->expectsOutputToContain('Resolved ')
            ->expectsOutputToContain('Static site generated successfully at')
            ->assertSuccessful();

        $this->assertFileExists($this->outputPath.'/index.html');
        $this->assertFileExists($this->outputPath.'/blog/index.html');
        $this->assertFileExists($this->outputPath.'/blog/'.$post->slug.'/index.html');
        $this->assertFileExists($this->outputPath.'/category/'.$category->slug.'/index.html');
        $this->assertFileExists($this->outputPath.'/about/index.html');
        $this->assertFileExists($this->outputPath.'/notes/index.html');
    }

    public function test_generate_static_site_exports_paginated_indexes(): void
    {
        $category = Category::factory()->create();
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);

        Post::factory()->published()->count(20)->create(['category_id' => $category->id]);
        Note::factory()->count(20)->create();

        $this->artisan('basecms:generate-static')
            ->assertSuccessful();

        $this->assertFileExists($this->outputPath.'/blog/page/2/index.html');
        $this->assertFileExists($this->outputPath.'/category/'.$category->slug.'/page/2/index.html');
        $this->assertFileExists($this->outputPath.'/notes/page/2/index.html');
        $this->assertStringContainsString('/blog/page/2/', file_get_contents($this->outputPath.'/blog/index.html'));
    }

    public function test_generate_static_site_reports_total_route_count_for_large_exports(): void
    {
        $category = Category::factory()->create();
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);

        Post::factory()->published()->count(20)->create(['category_id' => $category->id]);
        Note::factory()->count(20)->create();

        $this->artisan('basecms:generate-static')
            ->expectsOutputToContain('Resolved ')
            ->expectsOutputToContain('Exported ')
            ->assertSuccessful();
    }

    public function test_generate_static_site_skips_draft_pages_and_renders_builder_blocks(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);
        Page::factory()->draft()->create(['title' => 'Hidden', 'slug' => 'hidden']);
        Page::factory()->create([
            'title' => 'Builder Page',
            'slug' => 'builder-page',
            'use_builder' => true,
            'body' => 'Unused body',
            'blocks' => [
                [
                    'type' => 'markdown',
                    'data' => ['content' => 'Builder **content**'],
                ],
            ],
        ]);

        $this->artisan('basecms:generate-static')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($this->outputPath.'/hidden/index.html');
        $this->assertFileExists($this->outputPath.'/builder-page/index.html');
        $this->assertStringContainsString('Builder', file_get_contents($this->outputPath.'/builder-page/index.html'));
        $this->assertStringNotContainsString('Unused body', file_get_contents($this->outputPath.'/builder-page/index.html'));
    }

    public function test_generate_static_site_emits_legacy_redirects_and_optional_artifacts(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);
        $post = Post::factory()->published()->create();
        Note::factory()->create();

        $this->artisan('basecms:generate-static')
            ->assertSuccessful();

        $this->assertFileExists($this->outputPath.'/posts/index.html');
        $this->assertFileExists($this->outputPath.'/posts/'.$post->slug.'/index.html');
        $this->assertStringContainsString('/blog/', file_get_contents($this->outputPath.'/posts/index.html'));
        $this->assertFileExists($this->outputPath.'/sitemap.xml');
        $this->assertFileExists($this->outputPath.'/feed/posts/rss');
        $this->assertFileExists($this->outputPath.'/feed/notes/json');
    }

    public function test_generate_static_site_warns_and_skips_failed_routes(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);

        config()->set('basecms.static_site.exporters', [BrokenStaticRouteExporter::class]);

        $this->artisan('basecms:generate-static')
            ->expectsOutputToContain('Skipping [/missing-page] because it returned status [404].')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($this->outputPath.'/missing-page/index.html');
    }

    public function test_generate_static_site_applies_runtime_overrides_and_restores_original_state(): void
    {
        Page::factory()->homepage()->create();
        Page::factory()->create(['title' => 'Blog', 'slug' => 'blog']);
        Page::factory()->create(['title' => 'Notes', 'slug' => 'notes']);
        Post::factory()->published()->create();
        Note::factory()->create();

        config()->set('app.env', 'local');
        app()->instance('env', 'local');
        config()->set('app.debug', true);
        config()->set('boost.browser_logs_watcher', true);
        config()->set('basecms.visits.track_visits', true);

        $router = app(Router::class);
        $router->pushMiddlewareToGroup('web', InjectBoost::class);

        $this->artisan('basecms:generate-static')
            ->assertSuccessful();

        $this->assertStringNotContainsString(
            'browser-logger-active',
            file_get_contents($this->outputPath.'/blog/index.html')
        );
        $this->assertSame('local', config('app.env'));
        $this->assertTrue(config('app.debug'));
        $this->assertTrue(config('boost.browser_logs_watcher'));
        $this->assertTrue(config('basecms.visits.track_visits'));
        $this->assertSame('local', app()->environment());
        $this->assertContains(InjectBoost::class, $router->getMiddlewareGroups()['web']);
    }

    private function deleteDirectory(string $directory): void
    {
        $items = array_diff(scandir($directory) ?: [], ['.', '..']);

        foreach ($items as $item) {
            $path = $directory.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);

                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
