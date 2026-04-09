<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class CreateSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_site_command_aborts_early_when_multisite_is_disabled(): void
    {
        config()->set('basecms.multisite.enabled', false);

        $this->artisan('basecms:create-site')
            ->expectsOutput('Multi-site is disabled. Enable basecms.multisite.enabled before creating another site.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('sites', 0);
    }

    public function test_create_site_command_creates_a_site_when_multisite_is_enabled(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $this->artisan('basecms:create-site')
            ->expectsQuestion('Site name', 'Acme Publications')
            ->expectsQuestion('Site key', 'acme-publications')
            ->expectsOutput('Site name: Acme Publications.')
            ->expectsOutput('Site key: acme-publications.')
            ->assertSuccessful();

        $this->assertDatabaseHas('sites', [
            'name' => 'Acme Publications',
            'key' => 'acme-publications',
        ]);
    }

    public function test_create_site_command_allows_overriding_the_suggested_site_key(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $this->artisan('basecms:create-site')
            ->expectsQuestion('Site name', 'Acme Publications')
            ->expectsQuestion('Site key', 'acme')
            ->assertSuccessful();

        $this->assertDatabaseHas('sites', [
            'name' => 'Acme Publications',
            'key' => 'acme',
        ]);
    }

    public function test_create_site_command_does_not_create_a_user(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $this->artisan('basecms:create-site')
            ->expectsQuestion('Site name', 'Acme Publications')
            ->expectsQuestion('Site key', 'acme-publications')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
    }

    public function test_create_site_command_rejects_duplicate_site_keys(): void
    {
        config()->set('basecms.multisite.enabled', true);
        Site::factory()->create(['key' => 'acme-publications']);

        $this->artisan('basecms:create-site')
            ->expectsQuestion('Site name', 'Acme Publications')
            ->expectsQuestion('Site key', 'acme-publications')
            ->expectsOutputToContain('That site key is already in use.')
            ->assertExitCode(1);
    }
}
