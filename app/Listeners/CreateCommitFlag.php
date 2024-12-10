<?php

namespace App\Listeners;

use App\Events\PostPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class CreateCommitFlag
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PostPublished $event): void
    {
        info('Creating commit flag for post: ' . $event->post->title);

        file_put_contents(
            base_path('COMMIT'),
            $event->post->title,
            FILE_APPEND
        );
    }
}
