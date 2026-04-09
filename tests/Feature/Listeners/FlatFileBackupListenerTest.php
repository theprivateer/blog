<?php

namespace Tests\Feature\Listeners;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Listeners\FlatFileBackupListener;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Tests\TestCase;

class FlatFileBackupListenerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('basecms.flat_file_backup.enabled', true);

        $this->fakeContentDisk();

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

        Storage::disk('content')->assertExists($post->fresh()->filename);
        $this->assertFileExists(public_path('sitemap.xml'));
    }

    public function test_handle_post_deleted_removes_file(): void
    {
        $post = Post::factory()->published()->create();

        $listener = app(FlatFileBackupListener::class);

        Page::factory()->homepage()->create();
        $listener->handle(new PostSaved($post));

        $filename = $post->fresh()->filename;
        Storage::disk('content')->assertExists($filename);

        $listener->handle(new PostDeleted($post->fresh()));

        Storage::disk('content')->assertMissing($filename);
    }

    public function test_handle_post_saved_does_nothing_when_backups_are_disabled(): void
    {
        config()->set('basecms.flat_file_backup.enabled', false);

        Page::factory()->homepage()->create();
        $post = Post::factory()->published()->create();

        $listener = app(FlatFileBackupListener::class);
        $listener->handle(new PostSaved($post));

        Storage::disk('content')->assertMissing('default/posts/'.$post->getFlatFileFilename());
        $this->assertFileDoesNotExist(public_path('sitemap.xml'));
    }

    public function test_handle_post_deleted_does_nothing_when_backups_are_disabled(): void
    {
        $post = Post::factory()->published()->create();
        $listener = app(FlatFileBackupListener::class);

        Page::factory()->homepage()->create();
        $listener->handle(new PostSaved($post));

        $filename = $post->fresh()->filename;
        Storage::disk('content')->assertExists($filename);

        config()->set('basecms.flat_file_backup.enabled', false);

        $listener->handle(new PostDeleted($post->fresh()));

        Storage::disk('content')->assertExists($filename);
    }
}
