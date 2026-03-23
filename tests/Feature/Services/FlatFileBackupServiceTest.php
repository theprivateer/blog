<?php

namespace Tests\Feature\Services;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Category;
use App\Models\Metadata;
use App\Models\Note;
use App\Models\Page;
use App\Models\Post;
use App\Services\FlatFileBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FlatFileBackupServiceTest extends TestCase
{
    use RefreshDatabase;

    private FlatFileBackupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        Storage::fake('posts');
        Storage::fake('notes');
        Storage::fake('pages');
        Storage::fake('categories');

        $this->service = new FlatFileBackupService;
    }

    public function test_save_writes_markdown_file_for_post(): void
    {
        $post = Post::factory()->published()->create();

        $this->service->save($post);

        Storage::disk('posts')->assertExists($post->getFlatFileFilename());
    }

    public function test_save_writes_yaml_frontmatter_and_body(): void
    {
        $post = Post::factory()->create(['body' => 'Hello world']);

        $this->service->save($post);

        $content = Storage::disk('posts')->get($post->getFlatFileFilename());

        $this->assertStringStartsWith("---\n", $content);
        $this->assertStringContainsString($post->title, $content);
        $this->assertStringEndsWith('Hello world', $content);
    }

    public function test_save_includes_metadata_in_frontmatter(): void
    {
        $post = Post::factory()->create();
        Metadata::factory()->create([
            'parent_type' => Post::class,
            'parent_id' => $post->id,
            'title' => 'Meta Title',
            'description' => 'Meta Desc',
        ]);
        $post->load('metadata');

        $this->service->save($post);

        $content = Storage::disk('posts')->get($post->getFlatFileFilename());

        $this->assertStringContainsString('Meta Title', $content);
        $this->assertStringContainsString('Meta Desc', $content);
    }

    public function test_save_deletes_old_file_when_filename_changes(): void
    {
        $post = Post::factory()->unpublished()->create(['title' => 'Test']);

        $this->service->save($post);

        $oldFilename = $post->fresh()->filename;
        Storage::disk('posts')->assertExists($oldFilename);

        $post->published_at = now()->subDay();
        $post->saveQuietly();

        $this->service->save($post);

        Storage::disk('posts')->assertMissing($oldFilename);
        Storage::disk('posts')->assertExists($post->getFlatFileFilename());
    }

    public function test_save_updates_filename_column(): void
    {
        $post = Post::factory()->create();

        $this->service->save($post);

        $this->assertEquals($post->getFlatFileFilename(), $post->fresh()->filename);
    }

    public function test_delete_removes_file_from_disk(): void
    {
        $post = Post::factory()->create();

        $this->service->save($post);
        $post = $post->fresh();

        Storage::disk('posts')->assertExists($post->filename);

        $this->service->delete($post);

        Storage::disk('posts')->assertMissing($post->filename);
    }

    public function test_save_works_for_note(): void
    {
        $note = Note::factory()->create();

        $this->service->save($note);

        Storage::disk('notes')->assertExists($note->getFlatFileFilename());
    }

    public function test_save_works_for_page(): void
    {
        $page = Page::factory()->create();

        $this->service->save($page);

        Storage::disk('pages')->assertExists($page->getFlatFileFilename());
    }

    public function test_save_works_for_category(): void
    {
        $category = Category::factory()->create();

        $this->service->save($category);

        Storage::disk('categories')->assertExists($category->getFlatFileFilename());
    }
}
