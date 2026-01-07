<?php

namespace App\Services;

use App\Models\BacksUpToFlatFile;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

class FlatFileBackupService
{
    // Responsible for writing an post, page, note or moment database record to a Markdown file
    public function save(BacksUpToFlatFile $record): void
    {
        $content = '';

        if (count($record->getFrontmatterColumns()) > 0) {
            $content .= "---\n";

            $content .= Yaml::dump($record->only($record->getFrontmatterColumns()), 2);

            $content .= "---\n\n";
        }

        $content .= $record->body;

        Storage::disk($record->getDiskName())
            ->put($record->getFlatFileFilename(), $content);

        if ( ! is_null($record->filename) && $record->filename != $record->getFlatFileFilename()) {
            $this->delete($record);
        }

        $record->filename = $record->getFlatFileFilename();
        $record->saveQuietly();
    }

    public function delete($record)
    {
        Storage::disk($record->getDiskName())
                ->delete($record->filename);
    }
}
