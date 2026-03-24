<?php

namespace Tests\Feature\Models;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Spatie\Feed\FeedItem;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_slug_generated_from_title(): void
    {
        $note = Note::factory()->create(['title' => 'My First Note']);

        $this->assertEquals('my-first-note', $note->slug);
    }

    public function test_slug_not_updated_on_title_change(): void
    {
        $note = Note::factory()->create(['title' => 'Original']);
        $originalSlug = $note->slug;

        $note->update(['title' => 'Changed']);

        $this->assertEquals($originalSlug, $note->fresh()->slug);
    }

    public function test_get_disk_name_returns_notes(): void
    {
        $this->assertEquals('notes', (new Note)->getDiskName());
    }

    public function test_get_flat_file_filename_includes_created_at_and_slug(): void
    {
        $note = Note::factory()->create(['title' => 'My Note']);

        $filename = $note->getFlatFileFilename();

        $this->assertStringContainsString('my-note.md', $filename);
        $this->assertStringEndsWith('.md', $filename);
    }

    public function test_get_frontmatter_columns_returns_expected_keys(): void
    {
        $expected = ['title', 'link', 'created_at', 'updated_at'];

        $this->assertEquals($expected, (new Note)->getFrontmatterColumns());
    }

    public function test_dispatches_post_saved_event(): void
    {
        Note::factory()->create();

        Event::assertDispatched(PostSaved::class);
    }

    public function test_dispatches_post_deleted_event(): void
    {
        $note = Note::factory()->create();
        $note->delete();

        Event::assertDispatched(PostDeleted::class);
    }

    public function test_get_feed_items_returns_max_20(): void
    {
        Note::factory()->count(25)->create();

        $this->assertCount(20, Note::getFeedItems());
    }

    public function test_to_feed_item_returns_feed_item(): void
    {
        $note = Note::factory()->create(['title' => 'Feed Note']);

        $this->assertInstanceOf(FeedItem::class, $note->toFeedItem());
    }

    public function test_link_is_fillable_and_nullable(): void
    {
        $noteWithLink = Note::factory()->create(['link' => 'https://example.com']);
        $noteWithoutLink = Note::factory()->create(['link' => null]);

        $this->assertEquals('https://example.com', $noteWithLink->link);
        $this->assertNull($noteWithoutLink->link);
    }
}
