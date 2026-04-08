<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Post;
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

    public function test_category_routes_are_scoped_to_the_request_domain_when_multisite_is_enabled(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $alphaSite = $this->makeSite('alpha', 'alpha.test');
        $betaSite = $this->makeSite('beta', 'beta.test');

        $alphaCategory = Category::factory()->create(['site_id' => $alphaSite->id, 'slug' => 'shared-category', 'title' => 'Alpha Category']);
        $betaCategory = Category::factory()->create(['site_id' => $betaSite->id, 'slug' => 'shared-category', 'title' => 'Beta Category']);

        Post::factory()->published()->create(['site_id' => $alphaSite->id, 'category_id' => $alphaCategory->id, 'title' => 'Alpha Post']);
        Post::factory()->published()->create(['site_id' => $betaSite->id, 'category_id' => $betaCategory->id, 'title' => 'Beta Post']);

        $this->get('http://alpha.test/category/shared-category')
            ->assertOk()
            ->assertSee('Alpha Post')
            ->assertDontSee('Beta Post');
    }
}
