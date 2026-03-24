<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Models\Page;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Page::factory()->create(['is_homepage' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
