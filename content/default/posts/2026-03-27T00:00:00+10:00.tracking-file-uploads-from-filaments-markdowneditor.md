---
title: "Tracking File Uploads from Filament's MarkdownEditor"
intro: null
published_at: 2026-03-27T00:00:00+10:00
category_id: 2
created_at: 2026-03-27T08:31:20+10:00
updated_at: 2026-03-27T09:12:01+10:00
metadata:
    title: null
    description: 'Learn how to track Filament MarkdownEditor uploads in Laravel by storing asset records, linking files to content, and handling cleanup.'
---

The `MarkdownEditor` component that ships with [Filament](https://filamentphp.com) handles file uploads out of the box. Drop an image in, it gets stored to disk, and a URL is inserted into your markdown. Simple enough.

But what happens to those files after that? There's no database record. No way to know which post an image belongs to. No audit trail of who uploaded what. If you delete a post, its images quietly become orphans sitting on your S3 bucket forever.

I ran into this while building a Laravel package that uses Filament to manage blog content. I wanted every uploaded file tracked in the database, linked back to its parent post or page, and attributed to the user who uploaded it. The `MarkdownEditor` component has the hooks to make this work; you just need to wire them up.

## The hooks Filament gives you

`MarkdownEditor` exposes three methods for customising file attachment behaviour:

- `fileAttachmentsDisk()` sets which filesystem disk to store files on
- `saveUploadedFileAttachmentUsing()` overrides the default upload handler
- `getFileAttachmentUrlUsing()` controls how the inserted URL is resolved

The default behaviour stores files and returns a URL. By overriding `saveUploadedFileAttachmentUsing`, you can intercept that upload and do whatever you need: save a database record, generate thumbnails, transform the URL through an image service, or all three.

## The Asset model

First, a model to track uploads. Each record captures the file's storage location, metadata, and its relationship to the content it belongs to.

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Asset extends Model
{
    protected $fillable = [
        'disk',
        'path',
        'directory',
        'filename',
        'mime_type',
        'size',
        'visibility',
        'url',
        'field',
        'uploaded_by',
        'attachable_type',
        'attachable_id',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
```

The `attachable` morph relationship means an Asset can belong to a Post, a Page, a Category, or any other model. The `field` column records which MarkdownEditor on the form triggered the upload, useful when a resource has more than one.

The migration:

```php
Schema::create('assets', function (Blueprint $table) {
    $table->id();
    $table->string('disk');
    $table->string('path');
    $table->string('directory')->nullable();
    $table->string('filename');
    $table->string('mime_type')->nullable();
    $table->unsignedBigInteger('size')->nullable();
    $table->string('visibility')->default('public');
    $table->text('url');
    $table->string('field');
    $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
    $table->nullableMorphs('attachable');
    $table->timestamps();

    $table->unique(['disk', 'path']);
});
```

The unique constraint on `['disk', 'path']` prevents duplicate records if the same file somehow gets processed twice.

## Building the service

The service needs to do two things: configure a `MarkdownEditor` instance with custom upload handling, and perform the actual upload-and-record-creation when a file comes in.

Start with a constant for the storage disk. Hard-code a sensible default and change it when your environment needs something different:

```php
namespace App\Services;

use Filament\Forms\Components\MarkdownEditor;

class MarkdownEditorAssetService
{
    private const ATTACHMENTS_DISK = 's3';

    public static function attachmentDisk(): string
    {
        return self::ATTACHMENTS_DISK;
    }
}
```

Next, the upload handler. This receives Livewire's `TemporaryUploadedFile`, stores it to disk, and creates the Asset record:

```php

//...

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MarkdownEditorAssetService
{
	
	//...
		
	public function storeUploadedAttachment(
			TemporaryUploadedFile $file,
			MarkdownEditor $component,
			?Model $record = null,
	): Asset {
			$diskName = $component->getFileAttachmentsDiskName();
			$directory = $component->getFileAttachmentsDirectory();
			$path = $file->store($directory, $diskName);
			$disk = Storage::disk($diskName);

			rescue(
					fn (): bool => $disk->setVisibility($path, $component->getFileAttachmentsVisibility()),
					report: false,
			);

			return Asset::create([
					'disk' => $diskName,
					'path' => $path,
					'directory' => pathinfo($path, PATHINFO_DIRNAME) !== '.'
							? pathinfo($path, PATHINFO_DIRNAME)
							: null,
					'filename' => $file->getClientOriginalName(),
					'mime_type' => $file->getMimeType(),
					'size' => $file->getSize(),
					'visibility' => $component->getFileAttachmentsVisibility(),
					'url' => $disk->url($path),
					'field' => $component->getName(),
					'uploaded_by' => auth()->id(),
					'attachable_type' => $record?->exists ? $record::class : null,
					'attachable_id' => $record?->exists ? $record->getKey() : null,
			]);
	}
}
```

A few things to note here. The method reads its configuration from the component itself (disk name, directory, visibility) rather than hardcoding values. This means individual MarkdownEditor fields can override settings if needed.

The `rescue()` call around `setVisibility` is a pragmatic choice. Some storage drivers (local disk, for instance) don't support visibility settings and will throw. Wrapping it means the upload still succeeds.

The `$record` parameter might be null. When a user creates a new post and drags an image in before saving, there's no database record yet. The Asset gets created without an `attachable` link. You'd then associate it after the parent record is saved, or leave it unlinked and clean up orphans periodically.

Finally, wire it all together with a static method that configures any `MarkdownEditor` instance:

```php
class MarkdownEditorAssetService
{
	
	//...
		
	public static function configureEditor(MarkdownEditor $editor): MarkdownEditor
	{
			return $editor
					->fileAttachmentsDisk(self::attachmentDisk())
					->saveUploadedFileAttachmentUsing(function (
							TemporaryUploadedFile $file,
							MarkdownEditor $component,
							?Model $record = null,
					): Asset {
							return app(self::class)->storeUploadedAttachment($file, $component, $record);
					})
					->getFileAttachmentUrlUsing(fn (Asset $file): string => $file->url);
	}
}
```

The `saveUploadedFileAttachmentUsing` closure resolves the service through the container with `app(self::class)`. This keeps the static method free of constructor dependencies while still allowing the instance method to be testable.

The `getFileAttachmentUrlUsing` callback receives the Asset model (the return value of your upload handler) and tells Filament what URL to insert into the markdown. Here it returns the stored URL directly, but this is where you could transform it.

## Using it in a form schema

With the service built, applying it to a MarkdownEditor is one line:

```php
use App\Services\MarkdownEditorAssetService;
use Filament\Forms\Components\MarkdownEditor;

MarkdownEditorAssetService::configureEditor(
    MarkdownEditor::make('body')
        ->columnSpanFull()
),
```

This works anywhere you'd put a `MarkdownEditor`: resource forms, page builder blocks, standalone Livewire forms. The static factory pattern means you don't change how you compose your schemas.

## Other use cases

Intercepting file uploads opens up possibilities beyond just database tracking.

**Image transformation URLs.** Instead of returning the raw S3 URL, `getFileAttachmentUrlUsing` could return a URL through an image service like Imgix, Cloudinary, or Laravel's Glide package. Your stored files stay as originals while the URLs in your markdown point to optimised, resized versions:

```php
->getFileAttachmentUrlUsing(function (Asset $file): string {
    return 'https://your-domain.imgix.net/' . $file->path . '?w=800&auto=format';
})
```

**Async thumbnail generation.** The upload handler could dispatch a job to generate thumbnails or run image optimisation in the background:

```php
$asset = Asset::create([...]);

GenerateThumbnails::dispatch($asset);

return $asset;
```

**Cleanup.** With every upload tracked in the database, you can find orphaned assets (those with no `attachable` link after a grace period) and delete both the record and the file from storage. No more mystery images accumulating on S3.

## Testing

Testing the upload flow requires creating a `TemporaryUploadedFile`, which normally only exists inside a Livewire component lifecycle. The workaround is to spin up a minimal Livewire component in your test:

```php
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Livewire;

protected function makeTemporaryUploadedFile(string $filename): TemporaryUploadedFile
{
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
    $testComponent->set('upload', UploadedFile::fake()->image($filename));

    return $testComponent->get('upload');
}
```

With that helper, tests are standard Laravel feature tests. Fake the storage disk, act as a user, call the service, and assert against the database:

```php
use App\Services\MarkdownEditorAssetService;

public function test_store_uploaded_attachment_persists_file_and_creates_asset(): void
{
    Storage::fake(MarkdownEditorAssetService::attachmentDisk());
    $this->actingAs(User::factory()->create());

    $service = app(MarkdownEditorAssetService::class);
    $component = MarkdownEditor::make('body')
        ->fileAttachmentsDisk(MarkdownEditorAssetService::attachmentDisk())
        ->fileAttachmentsDirectory('markdown-attachments');

    $asset = $service->storeUploadedAttachment(
        $this->makeTemporaryUploadedFile('hero.png'),
        $component,
    );

    Storage::disk(MarkdownEditorAssetService::attachmentDisk())->assertExists($asset->path);
    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'disk' => MarkdownEditorAssetService::attachmentDisk(),
        'field' => 'body',
        'uploaded_by' => auth()->id(),
    ]);
}
```

## Wrapping up

Filament's `MarkdownEditor` does a lot of the heavy lifting for file uploads. The three hook methods give you full control over where files go and what happens when they arrive. Wrapping that behaviour in a service keeps your form schemas clean and puts the logic in one testable place.

The approach here tracks uploads in the database, but the pattern is the same whether you're generating thumbnails, transforming URLs, or enforcing upload policies. Override the upload handler, do your work, return something the URL resolver can use.