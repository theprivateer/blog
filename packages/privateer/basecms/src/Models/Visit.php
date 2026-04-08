<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Privateer\Basecms\Database\Factories\VisitFactory;
use Privateer\Basecms\Models\Concerns\BelongsToSite;

class Visit extends Model
{
    use BelongsToSite;

    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    protected $fillable = [
        'site_id',
        'path',
        'method',
        'ip_address',
        'session_id',
        'user_agent',
        'response_status',
        'visitor_type',
        'visitor_label',
        'classification_source',
    ];

    protected static function newFactory(): VisitFactory
    {
        return VisitFactory::new();
    }
}
