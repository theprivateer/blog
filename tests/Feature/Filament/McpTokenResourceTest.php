<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Privateer\Basecms\Filament\Resources\McpTokens\Pages\ManageMcpTokens;
use Privateer\Basecms\Models\McpToken;
use Tests\TestCase;

class McpTokenResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_manage_mcp_tokens_page_loads(): void
    {
        Livewire::test(ManageMcpTokens::class)->assertOk();
    }

    public function test_can_create_an_access_key_from_the_modal(): void
    {
        Livewire::test(ManageMcpTokens::class)
            ->callAction('create', data: [
                'name' => 'Agent key',
                'abilities' => ['posts:read', 'posts:write'],
            ])
            ->assertHasNoActionErrors();

        $token = McpToken::query()->where('name', 'Agent key')->firstOrFail();
        $this->assertSame(['posts:read', 'posts:write'], $token->abilities);
    }

    public function test_creating_an_access_key_requires_at_least_one_ability(): void
    {
        Livewire::test(ManageMcpTokens::class)
            ->callAction('create', data: [
                'name' => 'Agent key',
                'abilities' => [],
            ])
            ->assertHasActionErrors(['abilities']);

        $this->assertDatabaseCount('mcp_tokens', 0);
    }

    public function test_can_revoke_an_access_key_from_the_table(): void
    {
        ['model' => $token] = McpToken::generate('Agent key', ['*']);

        Livewire::test(ManageMcpTokens::class)
            ->callTableAction('delete', $token)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('mcp_tokens', ['id' => $token->id]);
    }
}
