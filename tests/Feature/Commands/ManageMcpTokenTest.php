<?php

namespace Tests\Feature\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Privateer\Basecms\Models\McpToken;
use Tests\TestCase;

class ManageMcpTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_action_generates_a_token(): void
    {
        $this->artisan('basecms:mcp-token', [
            'action' => 'create',
            '--name' => 'Agent',
            '--abilities' => 'posts:read,posts:write',
        ])->assertSuccessful();

        $this->assertDatabaseHas('mcp_tokens', ['name' => 'Agent']);
        $this->assertSame(['posts:read', 'posts:write'], McpToken::query()->firstOrFail()->abilities);
    }

    public function test_create_action_accepts_full_access_wildcard(): void
    {
        $this->artisan('basecms:mcp-token', [
            'action' => 'create',
            '--name' => 'Agent',
            '--abilities' => '*',
        ])->assertSuccessful();

        $this->assertSame(['*'], McpToken::query()->firstOrFail()->abilities);
    }

    public function test_create_action_requires_at_least_one_ability(): void
    {
        $this->artisan('basecms:mcp-token', [
            'action' => 'create',
            '--name' => 'Agent',
            '--abilities' => '',
        ])->assertFailed();

        $this->assertDatabaseCount('mcp_tokens', 0);
    }

    public function test_create_action_rejects_unknown_abilities(): void
    {
        $this->artisan('basecms:mcp-token', [
            'action' => 'create',
            '--name' => 'Agent',
            '--abilities' => 'posts:read,videos:read',
        ])->assertFailed();

        $this->assertDatabaseCount('mcp_tokens', 0);
    }

    public function test_list_action_shows_empty_state(): void
    {
        $this->artisan('basecms:mcp-token', ['action' => 'list'])
            ->expectsOutputToContain('No MCP access keys have been created.')
            ->assertSuccessful();
    }

    public function test_list_action_shows_created_tokens(): void
    {
        McpToken::generate('Agent', ['posts:read']);

        $this->artisan('basecms:mcp-token', ['action' => 'list'])
            ->expectsOutputToContain('Agent')
            ->assertSuccessful();
    }

    public function test_revoke_action_deletes_the_token(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['posts:read']);

        $this->artisan('basecms:mcp-token', ['action' => 'revoke', 'id' => $token->id])
            ->assertSuccessful();

        $this->assertDatabaseMissing('mcp_tokens', ['id' => $token->id]);
    }

    public function test_revoke_action_fails_for_unknown_id(): void
    {
        $this->artisan('basecms:mcp-token', ['action' => 'revoke', 'id' => 999999])
            ->assertFailed();
    }
}
