<?php

namespace App\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;

class Category extends \Privateer\Basecms\Models\Category
{
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];
}
