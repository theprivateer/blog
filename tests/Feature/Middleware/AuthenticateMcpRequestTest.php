<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use Privateer\Basecms\Models\McpToken;
use Tests\TestCase;

class AuthenticateMcpRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    protected function initializePayload(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2025-06-18',
                'capabilities' => [],
                'clientInfo' => ['name' => 'test', 'version' => '1.0'],
            ],
        ];
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->postJson('/mcp', $this->initializePayload())->assertStatus(401);
    }

    public function test_valid_access_key_authenticates_the_request(): void
    {
        ['plainText' => $plainText] = McpToken::generate('Agent', ['*']);

        $this->withHeader('Authorization', "Bearer {$plainText}")
            ->postJson('/mcp', $this->initializePayload())
            ->assertOk()
            ->assertJsonPath('result.serverInfo.name', 'Base CMS');
    }

    public function test_valid_access_key_updates_last_used_at(): void
    {
        ['model' => $token, 'plainText' => $plainText] = McpToken::generate('Agent', ['*']);

        $this->assertNull($token->last_used_at);

        $this->withHeader('Authorization', "Bearer {$plainText}")
            ->postJson('/mcp', $this->initializePayload())
            ->assertOk();

        $this->assertNotNull($token->fresh()->last_used_at);
    }

    public function test_invalid_access_key_is_rejected(): void
    {
        $this->withHeader('Authorization', 'Bearer not-a-real-token')
            ->postJson('/mcp', $this->initializePayload())
            ->assertStatus(401);
    }

    public function test_expired_access_key_is_rejected(): void
    {
        ['plainText' => $plainText] = McpToken::generate('Agent', ['*'], Carbon::now()->subMinute());

        $this->withHeader('Authorization', "Bearer {$plainText}")
            ->postJson('/mcp', $this->initializePayload())
            ->assertStatus(401);
    }

    public function test_oauth_authenticated_session_is_granted_default_abilities(): void
    {
        config()->set('basecms.mcp.oauth.enabled', true);
        config()->set('basecms.mcp.oauth.default_abilities', ['posts:read']);

        Passport::actingAs(User::factory()->create());

        $this->postJson('/mcp', $this->initializePayload())->assertOk();
    }

    public function test_oauth_is_ignored_when_disabled_in_config(): void
    {
        config()->set('basecms.mcp.oauth.enabled', false);

        Passport::actingAs(User::factory()->create());

        $this->postJson('/mcp', $this->initializePayload())->assertStatus(401);
    }
}
