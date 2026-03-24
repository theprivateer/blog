<?php

namespace App\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;

class Post extends \Privateer\Basecms\Models\Post
{
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];
}
