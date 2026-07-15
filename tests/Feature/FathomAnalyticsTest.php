<?php

namespace Tests\Feature;

use Tests\TestCase;

class FathomAnalyticsTest extends TestCase
{
    public function test_renders_snippet_when_production_and_site_id_configured(): void
    {
        config(['services.fathom.site_id' => 'XKHNAQFJ']);
        app()->instance('env', 'production');

        $this->blade('<x-fathom-analytics />')
            ->assertSee('https://cdn.usefathom.com/script.js', false)
            ->assertSee('data-site="XKHNAQFJ"', false);
    }

    public function test_does_not_render_snippet_when_site_id_missing(): void
    {
        config(['services.fathom.site_id' => null]);
        app()->instance('env', 'production');

        $this->blade('<x-fathom-analytics />')
            ->assertDontSee('usefathom.com');
    }

    public function test_does_not_render_snippet_outside_production(): void
    {
        config(['services.fathom.site_id' => 'XKHNAQFJ']);
        app()->instance('env', 'local');

        $this->blade('<x-fathom-analytics />')
            ->assertDontSee('usefathom.com');
    }
}
