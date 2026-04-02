<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;

class Visit extends \Privateer\Basecms\Models\Visit
{
    use Prunable;

    public function prunable(): Builder
    {
        return static::query()
            ->where('created_at', '<', now()->subDays(30));
    }
}
