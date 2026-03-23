<?php

namespace Tests\Feature\Filament;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        $this->actingAs(User::factory()->create());
    }

    public function test_list_posts_page_loads(): void
    {
        Livewire::test(ListPosts::class)->assertOk();
    }

    public function test_list_posts_displays_records(): void
    {
        $posts = Post::factory()->count(3)->create();

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords($posts);
    }

    public function test_list_posts_searches_by_title(): void
    {
        $target = Post::factory()->create(['title' => 'Unique Laravel Post']);
        $other = Post::factory()->create(['title' => 'Something Else']);

        Livewire::test(ListPosts::class)
            ->searchTable('Unique Laravel Post')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_post_page_loads(): void
    {
        Livewire::test(CreatePost::class)->assertOk();
    }

    public function test_can_create_post(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Test Post',
                'slug' => 'test-post',
                'body' => 'Post body content',
                'intro' => 'Intro text',
                'category_id' => $category->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'slug' => 'test-post',
        ]);
    }

    public function test_create_post_requires_title(): void
    {
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => null,
                'slug' => 'test',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    }

    public function test_create_post_requires_slug(): void
    {
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Test',
                'slug' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'required']);
    }

    public function test_edit_post_page_loads(): void
    {
        $post = Post::factory()->create();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->assertOk();
    }

    public function test_can_update_post(): void
    {
        $post = Post::factory()->create();

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Title',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_can_create_post_with_metadata(): void
    {
        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Post With Meta',
                'slug' => 'post-with-meta',
                'metadata.title' => 'SEO Title',
                'metadata.description' => 'SEO Description',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('metadata', [
            'title' => 'SEO Title',
            'description' => 'SEO Description',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_posts(): void
    {
        auth()->logout();

        $this->get('/admin/posts')->assertRedirect();
    }
}
