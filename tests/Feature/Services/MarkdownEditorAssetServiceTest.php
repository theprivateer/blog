<?php

namespace Tests\Feature\Services;

use App\Events\PostDeleted;
use App\Events\PostSaved;
use App\Models\Page;
use App\Models\User;
use App\Services\MarkdownEditorAssetService;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Livewire;
use Tests\TestCase;

class MarkdownEditorAssetServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PostSaved::class, PostDeleted::class]);
        Storage::fake('s3');
        config()->set('filesystems.disks.s3.url', 'https://files.example.test');
    }

    public function test_store_uploaded_attachment_persists_file_and_creates_unlinked_asset(): void
    {
        $this->actingAs(User::factory()->create());

        $service = app(MarkdownEditorAssetService::class);
        $component = MarkdownEditor::make('body')
            ->fileAttachmentsDisk('s3')
            ->fileAttachmentsDirectory('markdown-attachments');

        $asset = $service->storeUploadedAttachment(
            $this->makeTemporaryUploadedFile('hero-image.png'),
            $component,
        );

        Storage::disk('s3')->assertExists($asset->path);
        $this->assertDatabaseHas('assets', [
            'id' => $asset->id,
            'disk' => 's3',
            'field' => 'body',
            'uploaded_by' => auth()->id(),
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
            ->fileAttachmentsDisk('s3');
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
            'attachable_type' => Page::class,
            'attachable_id' => $page->id,
        ]);
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
