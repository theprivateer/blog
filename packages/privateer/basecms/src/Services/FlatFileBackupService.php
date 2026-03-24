<?php

namespace Privateer\Basecms\Services;

use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\BacksUpToFlatFile;
use Symfony\Component\Yaml\Yaml;

/**
 * Responsible for writing a post, page or note database record to a Markdown file.
 */
class FlatFileBackupService
{
    public function save(BacksUpToFlatFile $record): void
    {
        $content = '';

        if (count($record->getFrontmatterColumns()) > 0) {
            $content .= "---\n";

            $content .= Yaml::dump($record->only($record->getFrontmatterColumns()), 2);

            if ($record->metadata) {
                $content .= Yaml::dump(['metadata' => $record->metadata->toArray()], 2);
            }

            $content .= "---\n\n";
        }

        $content .= $record->body;

        Storage::disk($record->getDiskName())
            ->put($record->getFlatFileFilename(), $content);

        if (! is_null($record->filename) && $record->filename != $record->getFlatFileFilename()) {
            $this->delete($record);
        }

        $record->filename = $record->getFlatFileFilename();
        $record->saveQuietly();
    }

    public function delete(BacksUpToFlatFile $record): void
    {
        Storage::disk($record->getDiskName())
            ->delete($record->filename);
    }
}
