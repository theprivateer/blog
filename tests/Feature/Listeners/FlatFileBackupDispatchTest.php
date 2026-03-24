<?php

namespace Tests\Feature\Listeners;

use App\Models\Category;
use App\Models\Note;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlatFileBackupDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('posts');
        Storage::fake('notes');
        Storage::fake('pages');
        Storage::fake('categories');

        Page::factory()->homepage()->create([
            'title' => 'Home',
        ]);
    }

    protected function tearDown(): void
    {
        $sitemapPath = public_path('sitemap.xml');

        if (file_exists($sitemapPath)) {
            unlink($sitemapPath);
        }

        parent::tearDown();
    }

    public function test_saving_models_dispatches_backup_flow_and_writes_markdown_files(): void
    {
        $page = Page::factory()->create([
            'title' => 'About',
        ]);
        $category = Category::factory()->create([
            'title' => 'Laravel',
        ]);
        $post = Post::factory()->published()->create([
            'title' => 'Post With Backup',
            'category_id' => $category->id,
        ]);
        $note = Note::factory()->create([
            'title' => 'Note With Backup',
        ]);

        Storage::disk('pages')->assertExists($page->fresh()->filename);
        Storage::disk('categories')->assertExists($category->fresh()->filename);
        Storage::disk('posts')->assertExists($post->fresh()->filename);
        Storage::disk('notes')->assertExists($note->fresh()->filename);
        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_deleting_models_dispatches_backup_flow_and_removes_markdown_files(): void
    {
        $page = Page::factory()->create([
            'title' => 'About',
        ]);
        $category = Category::factory()->create([
            'title' => 'Laravel',
        ]);
        $post = Post::factory()->published()->create([
            'title' => 'Post With Backup',
            'category_id' => $category->id,
        ]);
        $note = Note::factory()->create([
            'title' => 'Note With Backup',
        ]);

        $pageFilename = $page->fresh()->filename;
        $categoryFilename = $category->fresh()->filename;
        $postFilename = $post->fresh()->filename;
        $noteFilename = $note->fresh()->filename;

        $post->fresh()->delete();
        $category->fresh()->delete();
        $page->fresh()->delete();
        $note->fresh()->delete();

        Storage::disk('pages')->assertMissing($pageFilename);
        Storage::disk('categories')->assertMissing($categoryFilename);
        Storage::disk('posts')->assertMissing($postFilename);
        Storage::disk('notes')->assertMissing($noteFilename);
    }
}
