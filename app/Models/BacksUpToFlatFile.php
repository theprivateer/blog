<?php

namespace App\Models;

interface BacksUpToFlatFile
{
    public function getDiskName(): string;

    public function getFrontmatterColumns(): array;

    public function getFlatFileFilename(): string;
}
