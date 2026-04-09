<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_domain_prefers_primary_flag(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->create(['domain' => 'secondary.test', 'is_primary' => false]);
        $primary = Domain::factory()->for($site)->create(['domain' => 'primary.test', 'is_primary' => true]);

        $this->assertTrue($primary->is($site->fresh()->primaryDomain()));
    }

    public function test_primary_url_uses_the_primary_domain(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->create(['domain' => 'site.test', 'is_primary' => true]);

        $this->assertSame('https://site.test', $site->fresh()->primaryUrl('https://example.test'));
    }

    public function test_first_domain_for_a_site_becomes_primary_even_when_created_as_non_primary(): void
    {
        $site = Site::factory()->create();

        $domain = Domain::factory()->for($site)->create([
            'domain' => 'first.test',
            'is_primary' => false,
        ]);

        $this->assertTrue($domain->fresh()->is_primary);
    }

    public function test_creating_a_new_primary_domain_clears_the_old_primary_for_the_same_site(): void
    {
        $site = Site::factory()->create();
        $originalPrimary = Domain::factory()->for($site)->create([
            'domain' => 'primary.test',
            'is_primary' => true,
        ]);
        $newPrimary = Domain::factory()->for($site)->create([
            'domain' => 'replacement.test',
            'is_primary' => true,
        ]);

        $this->assertFalse($originalPrimary->fresh()->is_primary);
        $this->assertTrue($newPrimary->fresh()->is_primary);
    }

    public function test_promoting_a_domain_to_primary_demotes_the_previous_primary(): void
    {
        $site = Site::factory()->create();
        $originalPrimary = Domain::factory()->for($site)->create([
            'domain' => 'primary.test',
            'is_primary' => true,
        ]);
        $candidate = Domain::factory()->for($site)->create([
            'domain' => 'candidate.test',
            'is_primary' => false,
        ]);

        $candidate->update(['is_primary' => true]);

        $this->assertFalse($originalPrimary->fresh()->is_primary);
        $this->assertTrue($candidate->fresh()->is_primary);
    }

    public function test_unsetting_the_only_primary_domain_keeps_it_primary(): void
    {
        $site = Site::factory()->create();
        $domain = Domain::factory()->for($site)->create([
            'domain' => 'primary.test',
            'is_primary' => true,
        ]);

        $domain->update(['is_primary' => false]);

        $this->assertTrue($domain->fresh()->is_primary);
    }

    public function test_deleting_the_primary_domain_promotes_another_remaining_domain(): void
    {
        $site = Site::factory()->create();
        $primary = Domain::factory()->for($site)->create([
            'domain' => 'primary.test',
            'is_primary' => true,
        ]);
        $secondary = Domain::factory()->for($site)->create([
            'domain' => 'secondary.test',
            'is_primary' => false,
        ]);

        $primary->delete();

        $this->assertTrue($secondary->fresh()->is_primary);
    }

    public function test_primary_domain_changes_do_not_affect_other_sites(): void
    {
        $alphaSite = Site::factory()->create();
        $betaSite = Site::factory()->create();

        $alphaPrimary = Domain::factory()->for($alphaSite)->create([
            'domain' => 'alpha-primary.test',
            'is_primary' => true,
        ]);
        $alphaCandidate = Domain::factory()->for($alphaSite)->create([
            'domain' => 'alpha-candidate.test',
            'is_primary' => false,
        ]);
        $betaPrimary = Domain::factory()->for($betaSite)->create([
            'domain' => 'beta-primary.test',
            'is_primary' => true,
        ]);

        $alphaCandidate->update(['is_primary' => true]);

        $this->assertFalse($alphaPrimary->fresh()->is_primary);
        $this->assertTrue($alphaCandidate->fresh()->is_primary);
        $this->assertTrue($betaPrimary->fresh()->is_primary);
    }
}
