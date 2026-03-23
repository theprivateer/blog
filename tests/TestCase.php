<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected string $testContentRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateContentDisks();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testContentRoot);

        parent::tearDown();
    }

    protected function isolateContentDisks(): void
    {
        $this->testContentRoot = storage_path('framework/testing/content/'.sha1(static::class.'::'.$this->name()));

        File::deleteDirectory($this->testContentRoot);
        File::ensureDirectoryExists($this->testContentRoot);

        foreach (['posts', 'notes', 'pages', 'categories'] as $disk) {
            config()->set("filesystems.disks.{$disk}.root", "{$this->testContentRoot}/{$disk}");
            Storage::forgetDisk($disk);
        }
    }
}
