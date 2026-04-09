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

        $relativePath = $this->relativePathFor($record);

        $content .= $record->body;

        Storage::disk('content')
            ->put($relativePath, $content);

        if (! is_null($record->filename) && $record->filename !== $relativePath) {
            $this->delete($record);
        }

        $record->filename = $relativePath;
        $record->saveQuietly();
    }

    public function delete(BacksUpToFlatFile $record): void
    {
        Storage::disk('content')
            ->delete($record->filename);
    }

    protected function relativePathFor(BacksUpToFlatFile $record): string
    {
        if (method_exists($record, 'loadMissing')) {
            $record->loadMissing('site');
        }

        $siteKey = (string) data_get($record, 'site.key', 'default');
        $directory = $record->getTable();

        return trim($siteKey.'/'.$directory.'/'.$record->getFlatFileFilename(), '/');
    }
}
