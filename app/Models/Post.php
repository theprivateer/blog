<?php

namespace App\Models;

use Spatie\Sheets\Sheet;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\Sheets\Facades\Sheets;

class Post extends Sheet implements Feedable
{
    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->getPath())
            ->title($this->title)
            ->summary($this->contents)
            ->updated($this->date)
            ->link(route('posts.show', $this->slug))
            ->authorName('Phil Stephens')
            ->authorEmail('hello@philstephens.com');
    }

    public static function getFeedItems()
    {
        return Sheets::collection('posts')
                    ->all()
                    ->sortByDesc('date');
    }
}
