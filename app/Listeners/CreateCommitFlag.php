<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Models\Slash;
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
        if ($event->post instanceof Slash || $event->type == 'update') {
            $message = 'Update: ' . $event->post->title;
        } else {
            $message = 'New Post: ' . $event->post->title;
        }
        file_put_contents(
            storage_path('app/COMMIT'),
            $message,
            FILE_APPEND
        );
    }
}
