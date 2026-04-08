<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Privateer\Basecms\Database\Factories\DomainFactory;

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    protected $fillable = [
        'site_id',
        'domain',
        'is_primary',
    ];

    protected static function newFactory(): DomainFactory
    {
        return DomainFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo((string) config('basecms.models.site', Site::class));
    }
}
