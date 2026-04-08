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
}
