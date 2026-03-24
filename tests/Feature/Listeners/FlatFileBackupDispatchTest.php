<?php

namespace Tests\Feature\Listeners;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class FlatFileBackupDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('basecms.flat_file_backup.enabled', true);

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

    public function test_saving_models_does_not_write_markdown_files_when_backups_are_disabled(): void
    {
        config()->set('basecms.flat_file_backup.enabled', false);

        $sitemapPath = public_path('sitemap.xml');

        if (file_exists($sitemapPath)) {
            unlink($sitemapPath);
        }

        $page = Page::factory()->create([
            'title' => 'About',
        ]);
        $category = Category::factory()->create([
            'title' => 'Laravel',
        ]);
        $post = Post::factory()->published()->create([
            'title' => 'Post Without Backup',
            'category_id' => $category->id,
        ]);
        $note = Note::factory()->create([
            'title' => 'Note Without Backup',
        ]);

        Storage::disk('pages')->assertMissing($page->getFlatFileFilename());
        Storage::disk('categories')->assertMissing($category->getFlatFileFilename());
        Storage::disk('posts')->assertMissing($post->getFlatFileFilename());
        Storage::disk('notes')->assertMissing($note->getFlatFileFilename());
        $this->assertFileDoesNotExist(public_path('sitemap.xml'));
    }

    public function test_deleting_models_does_not_remove_markdown_files_when_backups_are_disabled(): void
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

        config()->set('basecms.flat_file_backup.enabled', false);

        $post->fresh()->delete();
        $category->fresh()->delete();
        $page->fresh()->delete();
        $note->fresh()->delete();

        Storage::disk('pages')->assertExists($pageFilename);
        Storage::disk('categories')->assertExists($categoryFilename);
        Storage::disk('posts')->assertExists($postFilename);
        Storage::disk('notes')->assertExists($noteFilename);
    }
}
