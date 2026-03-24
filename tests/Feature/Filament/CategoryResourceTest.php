<?php

namespace Tests\Feature\Filament;

use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Resources\Categories\Pages\CreateCategory;
use Privateer\Basecms\Filament\Resources\Categories\Pages\EditCategory;
use Privateer\Basecms\Filament\Resources\Categories\Pages\ListCategories;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);

        $this->actingAs(User::factory()->create());
    }

    public function test_list_categories_page_loads(): void
    {
        Livewire::test(ListCategories::class)->assertOk();
    }

    public function test_list_categories_displays_records(): void
    {
        $categories = Category::factory()->count(3)->create();

        Livewire::test(ListCategories::class)
            ->assertCanSeeTableRecords($categories);
    }

    public function test_create_category_page_loads(): void
    {
        Livewire::test(CreateCategory::class)->assertOk();
    }

    public function test_can_create_category(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'title' => 'Laravel',
                'body' => 'Posts about Laravel',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'title' => 'Laravel',
        ]);
    }

    public function test_create_category_requires_title(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'title' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    }

    public function test_edit_category_page_loads(): void
    {
        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->assertOk();
    }

    public function test_can_update_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Category',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'title' => 'Updated Category',
        ]);
    }

    public function test_can_create_category_with_metadata(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'title' => 'Category With Meta',
                'metadata.title' => 'Cat SEO Title',
                'metadata.description' => 'Cat SEO Desc',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('metadata', [
            'title' => 'Cat SEO Title',
        ]);
    }

    public function test_list_categories_searches_by_title(): void
    {
        $target = Category::factory()->create(['title' => 'Unique Category']);
        $other = Category::factory()->create(['title' => 'Something Else']);

        Livewire::test(ListCategories::class)
            ->searchTable('Unique Category')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_can_delete_category(): void
    {
        $category = Category::factory()->create();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
