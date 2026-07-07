<?php

namespace Privateer\Basecms\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class McpToken extends Model
{
    protected $fillable = ['name', 'token', 'abilities', 'site_id', 'created_by', 'expires_at'];

    protected $hidden = ['token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @param  array<int, string>  $abilities
     * @return array{model: self, plainText: string}
     */
    public static function generate(string $name, array $abilities, ?Carbon $expiresAt = null, ?Site $site = null, ?Authenticatable $createdBy = null): array
    {
        $plainText = Str::random(40);

        $token = static::query()->create([
            'name' => $name,
            'token' => static::hashToken($plainText),
            'abilities' => $abilities,
            'site_id' => $site?->getKey(),
            'created_by' => $createdBy?->getAuthIdentifier(),
            'expires_at' => $expiresAt,
        ]);

        return ['model' => $token, 'plainText' => $plainText];
    }

    public static function findByPlainText(string $plainText): ?self
    {
        return static::query()
            ->where('token', static::hashToken($plainText))
            ->first();
    }

    protected static function hashToken(string $plainText): string
    {
        return hash('sha256', $plainText);
    }

    public function can(string $ability): bool
    {
        $abilities = $this->getAttribute('abilities') ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public function isValid(): bool
    {
        $expiresAt = $this->getAttribute('expires_at');

        return ! $expiresAt instanceof Carbon || $expiresAt->isFuture();
    }

    public function markUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->saveQuietly();
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo((string) config('basecms.models.site', Site::class));
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo((string) (config('basecms.models.user') ?? config('auth.providers.users.model')), 'created_by');
    }

    protected function abilitiesLabel(): Attribute
    {
        return Attribute::make(
            get: fn (): string => in_array('*', $this->getAttribute('abilities') ?? [], true)
                ? 'Full access'
                : implode(', ', $this->getAttribute('abilities') ?? []),
        );
    }
}
