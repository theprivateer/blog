<?php

namespace App\Services;

use App\Models\Asset;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MarkdownEditorAssetService
{
    public static function configureEditor(MarkdownEditor $editor): MarkdownEditor
    {
        return $editor
            ->saveUploadedFileAttachmentUsing(function (TemporaryUploadedFile $file, MarkdownEditor $component, ?Model $record = null): Asset {
                return app(self::class)->storeUploadedAttachment($file, $component, $record);
            })
            ->getFileAttachmentUrlUsing(fn (Asset $file): string => $file->url);
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
            'attachable_type' => $record?->exists ? $record::class : null,
            'attachable_id' => $record?->exists ? $record->getKey() : null,
        ]);
    }
}
