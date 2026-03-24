<?php

namespace Tests\Feature\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Asset;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AssetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
    }

    public function test_factory_creates_valid_asset(): void
    {
        $asset = Asset::factory()->create();

        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    public function test_size_is_cast_to_integer(): void
    {
        $asset = Asset::factory()->create(['size' => 12345]);

        $this->assertIsInt($asset->fresh()->size);
    }

    public function test_attachable_morph_to_relationship(): void
    {
        $page = Page::factory()->create();
        $asset = Asset::factory()->create([
            'attachable_type' => Page::class,
            'attachable_id' => $page->id,
        ]);

        $this->assertInstanceOf(Page::class, $asset->attachable);
        $this->assertEquals($page->id, $asset->attachable->id);
    }

    public function test_uploaded_by_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['uploaded_by' => $user->id]);

        $this->assertInstanceOf(User::class, $asset->uploadedBy);
        $this->assertEquals($user->id, $asset->uploadedBy->id);
    }

    public function test_attachable_can_be_null(): void
    {
        $asset = Asset::factory()->create([
            'attachable_type' => null,
            'attachable_id' => null,
        ]);

        $this->assertNull($asset->attachable);
    }

    public function test_uploaded_by_can_be_null(): void
    {
        $asset = Asset::factory()->create(['uploaded_by' => null]);

        $this->assertNull($asset->uploadedBy);
    }
}
