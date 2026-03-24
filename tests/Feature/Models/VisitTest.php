<?php

namespace Tests\Feature\Models;

use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_visit(): void
    {
        $visit = Visit::factory()->create();

        $this->assertDatabaseHas('visits', ['id' => $visit->id]);
    }

    public function test_fillable_attributes_are_mass_assignable(): void
    {
        $visit = Visit::factory()->create([
            'path' => '/blog',
            'method' => 'GET',
            'ip_address' => '127.0.0.1',
            'session_id' => 'test-session',
            'user_agent' => 'TestAgent/1.0',
        ]);

        $this->assertSame('/blog', $visit->path);
        $this->assertSame('GET', $visit->method);
        $this->assertSame('127.0.0.1', $visit->ip_address);
        $this->assertSame('test-session', $visit->session_id);
        $this->assertSame('TestAgent/1.0', $visit->user_agent);
    }
}
