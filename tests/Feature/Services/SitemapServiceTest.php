<?php

namespace Tests\Feature\Services;

use App\Models\Note;
use App\Services\SitemapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class SitemapServiceTest extends TestCase
{
    use RefreshDatabase;

    private SitemapService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        Page::factory()->homepage()->create();

        $this->service = app(SitemapService::class);
    }

    protected function tearDown(): void
    {
        $sitemapPath = public_path('sitemap.xml');
        if (file_exists($sitemapPath)) {
            unlink($sitemapPath);
        }

        parent::tearDown();
    }

    public function test_generate_creates_sitemap_xml(): void
    {
        $this->service->generate();

        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_sitemap_includes_homepage(): void
    {
        $this->service->generate();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString('<loc>'.config('app.url').'</loc>', $content);
    }

    public function test_sitemap_includes_non_draft_pages(): void
    {
        $page = Page::factory()->create(['title' => 'About', 'draft' => false]);

        $this->service->generate();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString($page->slug, $content);
    }

    public function test_sitemap_excludes_draft_pages(): void
    {
        $page = Page::factory()->draft()->create(['title' => 'Secret']);

        $this->service->generate();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringNotContainsString($page->slug, $content);
    }

    public function test_sitemap_includes_all_categories(): void
    {
        $category = Category::factory()->create(['title' => 'Laravel']);

        $this->service->generate();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString('category/'.$category->slug, $content);
    }

    public function test_sitemap_includes_only_published_posts(): void
    {
        $published = Post::factory()->published()->create(['title' => 'Published']);
        $unpublished = Post::factory()->unpublished()->create(['title' => 'Unpublished']);

        $this->service->generate();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString($published->slug, $content);
        $this->assertStringNotContainsString($unpublished->slug, $content);
    }

    public function test_sitemap_includes_all_notes(): void
    {
        $note = Note::factory()->create(['title' => 'My Note']);

        $this->service->generate();

        $content = file_get_contents(public_path('sitemap.xml'));

        $this->assertStringContainsString('notes/'.$note->id, $content);
    }
}
