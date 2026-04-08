<?php

namespace Tests\Feature\Services;

use App\Models\User;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Livewire;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\MarkdownEditorAssetService;
use Tests\TestCase;

class MarkdownEditorAssetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        config()->set('basecms.markdown_editor.attachments_disk', 's3');
        config()->set('basecms.multisite.enabled', true);
        Storage::fake(MarkdownEditorAssetService::attachmentDisk());
        config()->set('filesystems.disks.s3.url', 'https://files.example.test');
        $this->site = $this->actingOnTenant($this->makeSite());
    }

    public function test_store_uploaded_attachment_persists_file_and_creates_unlinked_asset(): void
    {
        $this->actingAs(User::factory()->create());

        $service = app(MarkdownEditorAssetService::class);
        $component = MarkdownEditor::make('body')
            ->fileAttachmentsDisk(MarkdownEditorAssetService::attachmentDisk())
            ->fileAttachmentsDirectory('markdown-attachments');

        $asset = $service->storeUploadedAttachment(
            $this->makeTemporaryUploadedFile('hero-image.png'),
            $component,
        );

        Storage::disk(MarkdownEditorAssetService::attachmentDisk())->assertExists($asset->path);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'disk' => MarkdownEditorAssetService::attachmentDisk(),
            'field' => 'body',
            'uploaded_by' => auth()->id(),
            'site_id' => $this->site->id,
            'attachable_type' => null,
            'attachable_id' => null,
        ]);
        $this->assertStringStartsWith('markdown-attachments/', $asset->path);
        $this->assertStringContainsString($asset->path, $asset->url);
    }

    public function test_store_uploaded_attachment_links_existing_record_when_present(): void
    {
        $this->actingAs(User::factory()->create());

        $service = app(MarkdownEditorAssetService::class);
        $component = MarkdownEditor::make('body')
            ->fileAttachmentsDisk(MarkdownEditorAssetService::attachmentDisk());
        $page = Page::factory()->create([
            'title' => 'Asset Target Page',
        ]);

        $asset = $service->storeUploadedAttachment(
            $this->makeTemporaryUploadedFile('linked-image.png'),
            $component,
            $page,
        );

        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'site_id' => $this->site->id,
            'attachable_type' => Page::class,
            'attachable_id' => $page->id,
        ]);
    }

    public function test_configure_editor_uses_the_configured_attachment_disk(): void
    {
        config()->set('basecms.markdown_editor.attachments_disk', 'local');

        $component = MarkdownEditorAssetService::configureEditor(
            MarkdownEditor::make('body')
        );

        $this->assertSame('local', $component->getFileAttachmentsDiskName());
    }

    protected function makeTemporaryUploadedFile(string $filename): TemporaryUploadedFile
    {
        $uploadedFile = UploadedFile::fake()->image($filename);

        $component = new class extends Component
        {
            use WithFileUploads;

            public $upload = null;

            public function render(): string
            {
                return '<div></div>';
            }
        };

        $testComponent = Livewire::test($component::class);

        $testComponent->set('upload', $uploadedFile);

        /** @var TemporaryUploadedFile $temporaryUploadedFile */
        $temporaryUploadedFile = $testComponent->get('upload');

        return $temporaryUploadedFile;
    }
}
