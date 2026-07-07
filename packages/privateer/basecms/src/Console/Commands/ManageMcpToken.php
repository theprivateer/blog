<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Privateer\Basecms\Console\Commands\Concerns\InteractsWithSelectedSite;
use Privateer\Basecms\Mcp\Support\ContentTypeRegistry;
use Privateer\Basecms\Models\McpToken;
use Privateer\Basecms\Models\Site;

class ManageMcpToken extends Command
{
    use InteractsWithSelectedSite;

    protected $signature = 'basecms:mcp-token
        {action : create, list, or revoke}
        {id? : The token id to revoke}
        {--name= : A label for the token}
        {--abilities= : Comma-separated abilities, e.g. posts:read,posts:write,analytics:read, or * for full access}
        {--expires= : An optional expiry date, e.g. 2026-12-31}
        {--site= : Restrict the token to a single site (multisite installs only)}';

    protected $description = 'Create, list, or revoke MCP access keys.';

    public function __construct(private readonly ContentTypeRegistry $registry)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'create' => $this->createToken(),
            'list' => $this->listTokens(),
            'revoke' => $this->revokeToken(),
            default => $this->invalidAction(),
        };
    }

    protected function createToken(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('Token name'));

        if ($name === '') {
            $this->error('A name is required.');

            return self::FAILURE;
        }

        $abilities = $this->resolveAbilities();

        if ($abilities === []) {
            $this->error('At least one ability is required. Valid abilities: '.implode(', ', [...$this->registry->allAbilities(), '*']));

            return self::FAILURE;
        }

        $invalidAbilities = array_diff($abilities, [...$this->registry->allAbilities(), '*']);

        if ($invalidAbilities !== []) {
            $this->error('Unknown abilities: '.implode(', ', $invalidAbilities));

            return self::FAILURE;
        }

        $expiresAt = $this->option('expires') ? Carbon::parse((string) $this->option('expires')) : null;
        $site = $this->resolveOptionalSite();

        ['model' => $token, 'plainText' => $plainText] = McpToken::generate($name, $abilities, $expiresAt, $site);

        $this->info('MCP access key created. Copy it now — it will not be shown again:');
        $this->line($plainText);
        $this->table(
            ['ID', 'Name', 'Abilities', 'Site', 'Expires'],
            [[$token->id, $token->name, implode(', ', $abilities), $site?->key ?? 'all sites', $expiresAt?->toDateString() ?? 'never']],
        );

        return self::SUCCESS;
    }

    protected function listTokens(): int
    {
        $tokens = McpToken::query()->latest()->get();

        if ($tokens->isEmpty()) {
            $this->info('No MCP access keys have been created.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Abilities', 'Last used', 'Expires'],
            $tokens->map(fn (McpToken $token): array => [
                $token->id,
                $token->name,
                implode(', ', $token->abilities ?? []),
                $token->last_used_at?->diffForHumans() ?? 'never',
                $token->expires_at?->toDateString() ?? 'never',
            ])->all(),
        );

        return self::SUCCESS;
    }

    protected function revokeToken(): int
    {
        $id = $this->argument('id');

        if (! $id) {
            $this->error('Provide the token id to revoke, e.g. basecms:mcp-token revoke 3.');

            return self::FAILURE;
        }

        $token = McpToken::query()->find($id);

        if (! $token) {
            $this->error("No MCP access key found with id [{$id}].");

            return self::FAILURE;
        }

        $token->delete();
        $this->info("Revoked MCP access key [{$token->name}] (id {$id}).");

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->error('Unknown action. Use one of: create, list, revoke.');

        return self::FAILURE;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveAbilities(): array
    {
        $raw = (string) ($this->option('abilities') ?: '');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    protected function resolveOptionalSite(): ?Site
    {
        $siteKey = $this->option('site');

        if (! is_string($siteKey) || $siteKey === '') {
            return null;
        }

        return $this->resolveSiteByKey($siteKey);
    }
}
