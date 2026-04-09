<?php

namespace Tests;

use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Site;

abstract class TestCase extends BaseTestCase
{
    protected string $testContentRoot;

    protected ?string $fakeContentRoot = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateContentDisks();
    }

    protected function tearDown(): void
    {
        Filament::setTenant(null, isQuiet: true);
        Storage::forgetDisk('content');
        if ($this->fakeContentRoot !== null) {
            File::deleteDirectory($this->fakeContentRoot);
            $this->fakeContentRoot = null;
        }
        File::deleteDirectory($this->testContentRoot);

        parent::tearDown();
    }

    protected function isolateContentDisks(): void
    {
        $this->testContentRoot = storage_path('framework/testing/content/'.sha1(static::class.'::'.$this->name()));

        File::deleteDirectory($this->testContentRoot);
        File::ensureDirectoryExists($this->testContentRoot);

        config()->set('filesystems.disks.content.root', $this->testContentRoot);
        Storage::forgetDisk('content');
    }

    protected function fakeContentDisk(): void
    {
        Storage::fake('content');

        $this->fakeContentRoot = Storage::disk('content')->path('');
    }

    protected function makeSite(string $key = 'default', string $domain = 'default.test'): Site
    {
        $site = Site::factory()->create([
            'key' => $key,
            'name' => ucfirst($key).' Site',
        ]);

        Domain::factory()->for($site)->create([
            'domain' => $domain,
            'is_primary' => true,
        ]);

        return $site;
    }

    protected function actingOnTenant(Site $site): Site
    {
        Filament::setTenant($site, isQuiet: true);

        return $site;
    }
}
