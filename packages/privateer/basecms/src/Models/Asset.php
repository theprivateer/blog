<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Privateer\Basecms\Database\Factories\AssetFactory;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'disk',
        'path',
        'directory',
        'filename',
        'mime_type',
        'size',
        'visibility',
        'url',
        'field',
        'uploaded_by',
        'attachable_type',
        'attachable_id',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo((string) (config('basecms.models.user') ?? config('auth.providers.users.model')), 'uploaded_by');
    }
}
