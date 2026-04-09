<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Privateer\Basecms\Filament\Resources\Domains\DomainResource;
use Privateer\Basecms\Filament\Resources\Domains\Pages\ManageDomains;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class DomainResourceTest extends TestCase
{
    use RefreshDatabase;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = $this->actingOnTenant($this->makeSite());
        $this->actingAs(User::factory()->create());
    }

    public function test_manage_domains_page_loads_when_multisite_is_enabled(): void
    {
        config()->set('basecms.multisite.enabled', true);

        Livewire::test(ManageDomains::class)->assertOk();
    }

    public function test_domain_resource_is_hidden_and_blocked_when_multisite_is_disabled(): void
    {
        config()->set('basecms.multisite.enabled', false);

        $this->assertFalse(DomainResource::shouldRegisterNavigation());
        $this->assertFalse(DomainResource::canAccess());

        Livewire::test(ManageDomains::class)->assertForbidden();
    }

    public function test_manage_domains_only_displays_records_for_the_active_tenant(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $visibleDomain = Domain::factory()->create([
            'site_id' => $this->site->id,
            'domain' => 'visible.test',
        ]);
        $hiddenDomain = Domain::factory()->create([
            'site_id' => Site::factory()->create()->id,
            'domain' => 'hidden.test',
        ]);

        Livewire::test(ManageDomains::class)
            ->assertCanSeeTableRecords([$visibleDomain])
            ->assertCanNotSeeTableRecords([$hiddenDomain]);
    }

    public function test_can_create_a_domain_from_the_modal(): void
    {
        config()->set('basecms.multisite.enabled', true);

        Livewire::test(ManageDomains::class)
            ->callAction('create', data: [
                'domain' => 'created.test',
                'is_primary' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('domains', [
            'site_id' => $this->site->id,
            'domain' => 'created.test',
            'is_primary' => true,
        ]);
    }

    public function test_can_edit_a_domain_from_the_modal(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $domain = Domain::factory()->create([
            'site_id' => $this->site->id,
            'domain' => 'before.test',
            'is_primary' => true,
        ]);

        Livewire::test(ManageDomains::class)
            ->callTableAction('edit', $domain, data: [
                'domain' => 'after.test',
                'is_primary' => true,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('domains', [
            'id' => $domain->id,
            'domain' => 'after.test',
            'is_primary' => true,
        ]);
    }

    public function test_can_delete_a_domain_from_the_modal(): void
    {
        config()->set('basecms.multisite.enabled', true);

        $domain = Domain::factory()->create([
            'site_id' => $this->site->id,
            'domain' => 'delete-me.test',
        ]);

        Livewire::test(ManageDomains::class)
            ->callTableAction('delete', $domain)
            ->assertHasNoActionErrors();

        $this->assertDatabaseMissing('domains', [
            'id' => $domain->id,
        ]);
    }
}
