<?php

use App\Models\Note;
use App\Models\Post;

return [
    'feeds' => [
        'posts_atom' => [
            'items' => [Post::class, 'getFeedItems'],
            'url' => '/feed/posts/atom',
            'title' => 'Phil Stephens - all posts',
            'description' => 'All of my posts',
            'language' => 'en-US',
            'format' => 'atom',
            'view' => 'feed::atom',
        ],
        'posts_rss' => [
            'items' => [Post::class, 'getFeedItems'],
            'url' => '/feed/posts/rss',
            'title' => 'Phil Stephens - all posts',
            'description' => 'All of my posts',
            'language' => 'en-US',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
        'posts_json' => [
            'items' => [Post::class, 'getFeedItems'],
            'url' => '/feed/posts/json',
            'title' => 'Phil Stephens - all posts',
            'description' => 'All of my posts',
            'language' => 'en-US',
            'format' => 'json',
            'view' => 'feed::json',
        ],
        'notes_atom' => [
            'items' => [Note::class, 'getFeedItems'],
            'url' => '/feed/notes/atom',
            'title' => 'Phil Stephens - all notes',
            'description' => 'All of my notes',
            'language' => 'en-US',
            'format' => 'atom',
            'view' => 'feed::atom',
        ],
        'notes_rss' => [
            'items' => [Note::class, 'getFeedItems'],
            'url' => '/feed/notes/rss',
            'title' => 'Phil Stephens - all notes',
            'description' => 'All of my notes',
            'language' => 'en-US',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
        'notes_json' => [
            'items' => [Note::class, 'getFeedItems'],
            'url' => '/feed/notes/json',
            'title' => 'Phil Stephens - all notes',
            'description' => 'All of my notes',
            'language' => 'en-US',
            'format' => 'json',
            'view' => 'feed::json',
        ],
    ],
];
