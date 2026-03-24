<?php

namespace Tests\Feature\Listeners;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Listeners\FlatFileBackupListener;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlatFileBackupListenerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('posts');
        Storage::fake('notes');
        Storage::fake('pages');
        Storage::fake('categories');

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

    public function test_handle_post_saved_writes_file_and_generates_sitemap(): void
    {
        $homepage = Page::factory()->homepage()->create();
        $post = Post::factory()->published()->create();

        $listener = app(FlatFileBackupListener::class);
        $listener->handle(new PostSaved($post));

        Storage::disk('posts')->assertExists($post->fresh()->filename);
        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_handle_post_deleted_removes_file(): void
    {
        $post = Post::factory()->published()->create();

        $listener = app(FlatFileBackupListener::class);

        Page::factory()->homepage()->create();
        $listener->handle(new PostSaved($post));

        $filename = $post->fresh()->filename;
        Storage::disk('posts')->assertExists($filename);

        $listener->handle(new PostDeleted($post->fresh()));

        Storage::disk('posts')->assertMissing($filename);
    }
}
