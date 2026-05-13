<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;

// Extends the package Visit model to add Prunable — the package model deliberately omits
// automatic pruning so host apps can choose their own retention policy.
class Visit extends \Privateer\Basecms\Models\Visit
{
    use Prunable;

    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<', now()->subDays(30));
    }
}
