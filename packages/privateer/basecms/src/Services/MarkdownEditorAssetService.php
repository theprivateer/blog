<?php

namespace Privateer\Basecms\Services;

use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Privateer\Basecms\Models\Asset;
use Privateer\Basecms\Models\Category;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;

class MarkdownEditorAssetService
{
    public static function configureEditor(MarkdownEditor $editor): MarkdownEditor
    {
        return $editor
            ->fileAttachmentsDisk(self::attachmentDisk())
            ->saveUploadedFileAttachmentUsing(function (TemporaryUploadedFile $file, MarkdownEditor $component, ?Model $record = null): Asset {
                return app(self::class)->storeUploadedAttachment($file, $component, $record);
            })
            ->getFileAttachmentUrlUsing(fn (Asset $file): string => $file->url);
    }

    public static function attachmentDisk(): string
    {
        return (string) config('basecms.markdown_editor.attachments_disk', 'local');
    }

    public function storeUploadedAttachment(TemporaryUploadedFile $file, MarkdownEditor $component, ?Model $record = null): Asset
    {
        $diskName = $component->getFileAttachmentsDiskName();
        $directory = $component->getFileAttachmentsDirectory();
        $path = $file->store($directory, $diskName);
        $disk = Storage::disk($diskName);

        rescue(fn (): bool => $disk->setVisibility($path, $component->getFileAttachmentsVisibility()), report: false);

        return Asset::create([
            'disk' => $diskName,
            'path' => $path,
            'directory' => pathinfo($path, PATHINFO_DIRNAME) !== '.' ? pathinfo($path, PATHINFO_DIRNAME) : null,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'visibility' => $component->getFileAttachmentsVisibility(),
            'url' => $disk->url($path),
            'field' => $component->getName(),
            'uploaded_by' => auth()->id(),
            'attachable_type' => $this->resolveAttachableType($record),
            'attachable_id' => $record?->exists ? $record->getKey() : null,
        ]);
    }

    protected function resolveAttachableType(?Model $record): ?string
    {
        if (! $record?->exists) {
            return null;
        }

        return match ($record::class) {
            Post::class => (string) config('basecms.models.post', Post::class),
            Page::class => (string) config('basecms.models.page', Page::class),
            Category::class => (string) config('basecms.models.category', Category::class),
            default => $record::class,
        };
    }
}
