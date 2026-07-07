<?php

namespace Tests\Feature\OAuth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Tests\TestCase;

class AuthorizationViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_oauth_authorize_screen_renders_for_an_authenticated_user(): void
    {
        // Regression test: Passport does not ship a default authorization view, so
        // Laravel\Passport\Contracts\AuthorizationViewResponse must be bound via
        // Passport::authorizationView() (see AppServiceProvider::boot()), or this
        // route throws a BindingResolutionException instead of rendering a screen.
        $client = Client::factory()->create([
            'redirect_uris' => ['https://claude.ai/api/mcp/auth_callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
        ]);

        $codeVerifier = Str::random(64);
        $codeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        $response = $this->actingAs(User::factory()->create())->get('/oauth/authorize?'.http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => 'https://claude.ai/api/mcp/auth_callback',
            'response_type' => 'code',
            'scope' => 'mcp:use',
            'state' => 'test-state',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]));

        $response->assertOk();
        $response->assertSee($client->name);
        $response->assertSee('Use MCP server');
    }
}
