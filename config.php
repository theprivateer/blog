<?php

use Illuminate\Support\Str;

return [
    'production' => false,
    'baseUrl' => '',
    'title' => 'Phil Stephens',
    'description' => 'Software developer and apsiring plant-based athlete',
    'collections' => [
        'posts' => [
            'author' => 'Phil Stephens',
            'sort' => '-date',
            'extends' => '_layouts.post',
            'path' => function ($page) {
                $slug = Str::slug($page->getFilename());

                $prefix = '';
                if ($page->link) {
                    $prefix = 'linked/';
                }
                if (substr($slug, 0, 2) == '20' && substr($slug, 4, 1) == '-') {
                    $slug = substr($slug, 11);
                }

                return $prefix . date('Y', $page->date) . '/' . date('m', $page->date) . '/' . $slug;
            },
            'filter' => function ($item) {
                return $item->date;
            },
        ],
    ],
];
