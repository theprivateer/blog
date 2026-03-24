<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_category_show_returns_ok(): void
    {
        $category = Category::factory()->create();

        $response = $this->get('/category/'.$category->slug);

        $response->assertStatus(200);
        $response->assertViewIs('categories.show');
    }

    public function test_category_show_displays_only_published_posts_in_category(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $published = Post::factory()->published()->create([
            'title' => 'Published Here',
            'category_id' => $category->id,
        ]);
        $unpublished = Post::factory()->unpublished()->create([
            'title' => 'Unpublished Here',
            'category_id' => $category->id,
        ]);
        $otherPost = Post::factory()->published()->create([
            'title' => 'Other Category',
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->get('/category/'.$category->slug);

        $response->assertSee('Published Here');
        $response->assertDontSee('Unpublished Here');
        $response->assertDontSee('Other Category');
    }

    public function test_category_show_paginates(): void
    {
        $category = Category::factory()->create();
        Post::factory()->published()->count(20)->create(['category_id' => $category->id]);

        $response = $this->get('/category/'.$category->slug);
        $response->assertStatus(200);

        $response = $this->get('/category/'.$category->slug.'?page=2');
        $response->assertStatus(200);
    }

    public function test_category_show_returns_404_for_nonexistent(): void
    {
        $response = $this->get('/category/nonexistent');

        $response->assertStatus(404);
    }
}
