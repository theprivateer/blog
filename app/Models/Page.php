<?php

namespace App\Models;

use App\Events\PostDeleted;
use App\Events\PostSaved;

class Page extends \Privateer\Basecms\Models\Page
{
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];
}
