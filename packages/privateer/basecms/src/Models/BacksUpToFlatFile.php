<?php

namespace Privateer\Basecms\Models;

interface BacksUpToFlatFile
{
    public function getFrontmatterColumns(): array;

    public function getFlatFileFilename(): string;
}
