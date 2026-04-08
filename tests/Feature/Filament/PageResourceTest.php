<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Builder;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Filament\Blocks\PageBuilder\HeaderBlock;
use Privateer\Basecms\Filament\Blocks\PageBuilder\MarkdownBlock;
use Privateer\Basecms\Filament\Resources\Pages\Pages\CreatePage;
use Privateer\Basecms\Filament\Resources\Pages\Pages\EditPage;
use Privateer\Basecms\Filament\Resources\Pages\Pages\ListPages;
use Privateer\Basecms\Models\Asset;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\GenerateMetaDescriptionAgent;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('basecms.pages.builder.enabled', true);
        config()->set('basecms.pages.builder.blocks', [MarkdownBlock::class, HeaderBlock::class]);
        config()->set('basecms.markdown_editor.attachments_disk', 's3');
        config()->set('basecms.ai.generate_meta_descriptions.enabled', true);
        config()->set('basecms.multisite.enabled', true);

        Event::fake([PostSaved::class, PostDeleted::class]);
        Storage::fake('s3');
        config()->set('filesystems.disks.s3.url', 'https://files.example.test');

        $this->site = $this->actingOnTenant($this->makeSite());
        $this->actingAs(User::factory()->create());
    }

    public function test_list_pages_page_loads(): void
    {
        Livewire::test(ListPages::class)->assertOk();
    }

    public function test_list_pages_displays_records(): void
    {
        $pages = Page::factory()->count(3)->create();

        Livewire::test(ListPages::class)
            ->assertCanSeeTableRecords($pages);
    }

    public function test_create_page_page_loads(): void
    {
        Livewire::test(CreatePage::class)->assertOk();
    }

    public function test_builder_fields_are_not_registered_when_page_builder_is_disabled(): void
    {
        config()->set('basecms.pages.builder.enabled', false);

        Livewire::test(CreatePage::class)
            ->assertFormFieldDoesNotExist('use_builder')
            ->assertFormFieldExists('body')
            ->assertFormFieldDoesNotExist('blocks');
    }

    public function test_builder_toggle_controls_body_and_blocks_fields(): void
    {
        Livewire::test(CreatePage::class)
            ->assertFormFieldVisible('use_builder')
            ->assertFormFieldVisible('body')
            ->assertFormFieldHidden('blocks')
            ->fillForm([
                'use_builder' => true,
            ])
            ->assertFormFieldHidden('body')
            ->assertFormFieldVisible('blocks');
    }

    public function test_can_create_page(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'About Us',
                'body' => 'Page body content',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'title' => 'About Us',
        ]);
    }

    public function test_can_create_builder_backed_page(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Builder Page',
                'use_builder' => true,
                'blocks' => [
                    [
                        'type' => 'markdown',
                        'data' => [
                            'content' => 'Builder block content',
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $page = Page::query()->where('title', 'Builder Page')->sole();

        $this->assertTrue($page->use_builder);
        $this->assertSame([
            [
                'type' => 'markdown',
                'data' => [
                    'content' => 'Builder block content',
                ],
            ],
        ], $page->blocks);
    }

    public function test_builder_blocks_are_loaded_from_config(): void
    {
        Livewire::test(CreatePage::class)
            ->assertFormFieldExists('blocks', checkFieldUsing: function (Builder $field): bool {
                $blocks = collect($field->getChildComponents())
                    ->mapWithKeys(fn ($block) => [$block->getName() => (string) $block->getLabel()])
                    ->all();

                return $blocks === [
                    'markdown' => 'Markdown',
                    'header' => 'Header',
                ];
            });
    }

    public function test_create_page_requires_title(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required']);
    }

    public function test_edit_page_page_loads(): void
    {
        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertOk();
    }

    public function test_can_update_page(): void
    {
        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'title' => 'Updated Page',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Page',
        ]);
    }

    public function test_can_create_page_with_metadata(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Page With Meta',
                'metadata.title' => 'Page SEO Title',
                'metadata.description' => 'Page SEO Desc',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('metadata', [
            'title' => 'Page SEO Title',
        ]);
    }

    public function test_markdown_editor_upload_creates_unlinked_asset_on_create_page(): void
    {
        Livewire::test(CreatePage::class)
            ->set('componentFileAttachments.data.body', UploadedFile::fake()->create('create-page-asset.png', 100, 'image/png'))
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $asset = Asset::query()->sole();

        Storage::disk('s3')->assertExists($asset->path);
        $this->assertSame('body', $asset->field);
        $this->assertSame($this->site->id, $asset->site_id);
        $this->assertNull($asset->attachable_type);
        $this->assertNull($asset->attachable_id);
    }

    public function test_markdown_editor_upload_links_asset_on_edit_page(): void
    {
        $page = Page::factory()->create([
            'title' => 'Existing Page',
        ]);

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->set('componentFileAttachments.data.body', UploadedFile::fake()->create('edit-page-asset.png', 100, 'image/png'))
            ->call('callSchemaComponentMethod', 'form.body', 'saveUploadedFileAttachmentAndGetUrl');

        $asset = Asset::query()->sole();

        $this->assertSame(Page::class, $asset->attachable_type);
        $this->assertSame($page->id, $asset->attachable_id);
        $this->assertSame($this->site->id, $asset->site_id);
    }

    public function test_can_set_homepage_toggle(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Home',
                'is_homepage' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'title' => 'Home',
            'is_homepage' => true,
        ]);
    }

    public function test_edit_page_exposes_meta_description_generation_action(): void
    {
        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertActionExists('generateMetaDescription');
    }

    public function test_edit_page_hides_meta_description_generation_action_when_disabled(): void
    {
        config()->set('basecms.ai.generate_meta_descriptions.enabled', false);

        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->assertActionDoesNotExist('generateMetaDescription');
    }

    public function test_generate_meta_description_action_replaces_existing_form_value_for_pages(): void
    {
        $page = Page::factory()->create([
            'title' => 'Saved page title',
            'body' => 'Saved page body',
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Clear summary of the revised page content that fits search snippets well and leaves the page title out of the description text.'],
        ])->preventStrayPrompts();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'title' => 'Draft page title',
                'body' => 'Draft page body with useful detail for visitors.',
                'metadata.description' => 'Old page description',
            ])
            ->callAction('generateMetaDescription')
            ->assertFormSet([
                'metadata.description' => 'Clear summary of the revised page content that fits search snippets well and leaves the page title out of the description text.',
            ])
            ->assertNotified('Meta description generated');
    }

    public function test_generate_meta_description_action_uses_builder_content_for_pages(): void
    {
        $page = Page::factory()->create([
            'title' => 'Saved page title',
            'body' => 'Saved page body',
        ]);

        GenerateMetaDescriptionAgent::fake([
            ['description' => 'Brief search-ready summary of the builder-driven content that explains the page clearly without echoing its title back to readers.'],
        ])->preventStrayPrompts();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'title' => 'Builder page title',
                'use_builder' => true,
                'blocks' => [
                    [
                        'type' => 'header',
                        'data' => [
                            'content' => 'Builder heading',
                            'level' => '2',
                        ],
                    ],
                    [
                        'type' => 'markdown',
                        'data' => [
                            'content' => 'Builder body copy.',
                        ],
                    ],
                ],
                'metadata.description' => 'Old page description',
            ])
            ->callAction('generateMetaDescription')
            ->assertFormSet([
                'metadata.description' => 'Brief search-ready summary of the builder-driven content that explains the page clearly without echoing its title back to readers.',
            ])
            ->assertNotified('Meta description generated');

        GenerateMetaDescriptionAgent::assertPrompted(function ($prompt): bool {
            return str_contains($prompt->prompt, 'Builder heading')
                && str_contains($prompt->prompt, 'Builder body copy.');
        });
    }

    public function test_generate_meta_description_action_warns_when_page_content_is_blank(): void
    {
        $page = Page::factory()->create();

        GenerateMetaDescriptionAgent::fake()->preventStrayPrompts();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->fillForm([
                'title' => '   ',
                'body' => '   ',
                'metadata.description' => 'Existing description',
            ])
            ->callAction('generateMetaDescription')
            ->assertFormSet([
                'metadata.description' => 'Existing description',
            ])
            ->assertNotified('Meta description was not generated');

        Notification::assertNotNotified('Meta description generated');
        GenerateMetaDescriptionAgent::assertNeverPrompted();
    }

    public function test_can_set_draft_toggle(): void
    {
        Livewire::test(CreatePage::class)
            ->fillForm([
                'title' => 'Draft Page',
                'draft' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('pages', [
            'title' => 'Draft Page',
            'draft' => true,
        ]);
    }

    public function test_list_pages_searches_by_title(): void
    {
        $target = Page::factory()->create(['title' => 'Unique Search Page']);
        $other = Page::factory()->create(['title' => 'Something Else']);

        Livewire::test(ListPages::class)
            ->searchTable('Unique Search Page')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_can_delete_page(): void
    {
        $page = Page::factory()->create();

        Livewire::test(EditPage::class, ['record' => $page->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }
}
