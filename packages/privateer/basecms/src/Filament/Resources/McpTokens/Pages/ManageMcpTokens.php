<?php

namespace Privateer\Basecms\Filament\Resources\McpTokens\Pages;

use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Privateer\Basecms\Filament\Resources\McpTokens\McpTokenResource;
use Privateer\Basecms\Models\McpToken;
use Privateer\Basecms\Models\Site;

class ManageMcpTokens extends ManageRecords
{
    protected static string $resource = McpTokenResource::class;

    protected function getHeaderActions(): array
    {
        // Captured by reference so the plaintext key generated in using() is still
        // available to after() — it's never persisted, so this closure is the only
        // place it exists once the response is sent.
        $plainTextToken = null;

        return [
            CreateAction::make()
                ->using(function (array $data) use (&$plainTextToken): Model {
                    $site = filled($data['site_id'] ?? null)
                        ? Site::query()->find($data['site_id'])
                        : null;

                    $expiresAt = filled($data['expires_at'] ?? null)
                        ? Carbon::parse($data['expires_at'])
                        : null;

                    ['model' => $token, 'plainText' => $plainTextToken] = McpToken::generate(
                        name: $data['name'],
                        abilities: $data['abilities'] ?? [],
                        expiresAt: $expiresAt,
                        site: $site,
                        createdBy: auth()->user(),
                    );

                    return $token;
                })
                ->after(function () use (&$plainTextToken): void {
                    Notification::make()
                        ->title('MCP access key created')
                        ->body("Copy this key now — it will not be shown again:\n\n{$plainTextToken}")
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
