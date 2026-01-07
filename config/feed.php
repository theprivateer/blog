<?php

use App\Models\Note;
use App\Models\Post;
use App\Models\Moment;

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
        'moments_atom' => [
            'items' => [Moment::class, 'getFeedItems'],
            'url' => '/feed/moments/atom',
            'title' => 'Phil Stephens - all moments',
            'description' => 'All of my moments',
            'language' => 'en-US',
            'format' => 'atom',
            'view' => 'feed::atom',
        ],
        'moments_rss' => [
            'items' => [Moment::class, 'getFeedItems'],
            'url' => '/feed/moments/rss',
            'title' => 'Phil Stephens - all moments',
            'description' => 'All of my moments',
            'language' => 'en-US',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
        'moments_json' => [
            'items' => [Moment::class, 'getFeedItems'],
            'url' => '/feed/moments/json',
            'title' => 'Phil Stephens - all moments',
            'description' => 'All of my moments',
            'language' => 'en-US',
            'format' => 'json',
            'view' => 'feed::json',
        ],
    ],
];
