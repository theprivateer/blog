<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Privateer\Basecms\Models\McpToken;
use Tests\TestCase;

class McpTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_creates_a_token_and_returns_the_plain_text_once(): void
    {
        ['model' => $token, 'plainText' => $plainText] = McpToken::generate('Agent', ['posts:read']);

        $this->assertDatabaseHas('mcp_tokens', ['id' => $token->id, 'name' => 'Agent']);
        $this->assertNotSame($plainText, $token->getAttribute('token'));
        $this->assertSame(['posts:read'], $token->abilities);
    }

    public function test_find_by_plain_text_returns_the_matching_token(): void
    {
        ['plainText' => $plainText] = McpToken::generate('Agent', ['posts:read']);

        $found = McpToken::findByPlainText($plainText);

        $this->assertNotNull($found);
        $this->assertSame('Agent', $found->name);
    }

    public function test_find_by_plain_text_returns_null_for_an_unknown_token(): void
    {
        $this->assertNull(McpToken::findByPlainText('not-a-real-token'));
    }

    public function test_can_returns_true_for_wildcard_ability(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['*']);

        $this->assertTrue($token->can('posts:delete'));
        $this->assertTrue($token->can('anything:at-all'));
    }

    public function test_can_returns_true_for_an_exact_ability(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['posts:read']);

        $this->assertTrue($token->can('posts:read'));
    }

    public function test_can_returns_false_for_a_missing_ability(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['posts:read']);

        $this->assertFalse($token->can('posts:write'));
    }

    public function test_is_valid_returns_true_when_there_is_no_expiry(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['*']);

        $this->assertTrue($token->isValid());
    }

    public function test_is_valid_returns_false_once_expired(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['*'], Carbon::now()->subMinute());

        $this->assertFalse($token->isValid());
    }

    public function test_is_valid_returns_true_before_expiry(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['*'], Carbon::now()->addDay());

        $this->assertTrue($token->isValid());
    }

    public function test_mark_used_updates_last_used_at(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['*']);

        $this->assertNull($token->last_used_at);

        $token->markUsed();

        $this->assertNotNull($token->fresh()->last_used_at);
    }

    public function test_token_hash_is_hidden_from_array_representation(): void
    {
        ['model' => $token] = McpToken::generate('Agent', ['*']);

        $this->assertArrayNotHasKey('token', $token->toArray());
    }
}
