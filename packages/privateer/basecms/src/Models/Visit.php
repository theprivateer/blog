<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Privateer\Basecms\Database\Factories\VisitFactory;

class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    protected $fillable = [
        'path',
        'method',
        'ip_address',
        'session_id',
        'user_agent',
        'visitor_type',
        'visitor_label',
        'classification_source',
    ];

    protected static function newFactory(): VisitFactory
    {
        return VisitFactory::new();
    }
}
