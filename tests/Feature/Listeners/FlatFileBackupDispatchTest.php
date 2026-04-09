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

        $this->fakeContentDisk();

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

        Storage::disk('content')->assertExists($page->fresh()->filename);
        Storage::disk('content')->assertExists($category->fresh()->filename);
        Storage::disk('content')->assertExists($post->fresh()->filename);
        Storage::disk('content')->assertExists($note->fresh()->filename);
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

        Storage::disk('content')->assertMissing($pageFilename);
        Storage::disk('content')->assertMissing($categoryFilename);
        Storage::disk('content')->assertMissing($postFilename);
        Storage::disk('content')->assertMissing($noteFilename);
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

        Storage::disk('content')->assertMissing('default/pages/'.$page->getFlatFileFilename());
        Storage::disk('content')->assertMissing('default/categories/'.$category->getFlatFileFilename());
        Storage::disk('content')->assertMissing('default/posts/'.$post->getFlatFileFilename());
        Storage::disk('content')->assertMissing('default/notes/'.$note->getFlatFileFilename());
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

        Storage::disk('content')->assertExists($pageFilename);
        Storage::disk('content')->assertExists($categoryFilename);
        Storage::disk('content')->assertExists($postFilename);
        Storage::disk('content')->assertExists($noteFilename);
    }
}
