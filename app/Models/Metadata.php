<?php

namespace App\Models;

use Database\Factories\MetadataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Metadata extends Model
{
    /** @use HasFactory<MetadataFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description'];

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array{title: string|null, description: string|null}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
