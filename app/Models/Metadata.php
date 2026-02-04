<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Metadata extends Model
{
    /** @use HasFactory<\Database\Factories\MetadataFactory> */
    use HasFactory;

    protected $fillable = ['title', 'description'];

    public function parent()
    {
        return $this->morphTo();
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
