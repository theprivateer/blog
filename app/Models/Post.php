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
        $reply = '<p><a href="mailto:hello@philstephens.com?subject=Reply: ' . $this->title . '">Reply by email</a></p>';

        return FeedItem::create()
            ->id($this->getPath())
            ->title($this->title)
            ->summary($this->contents . $reply)
            ->updated($this->date)
            ->link($this->link ?? route('posts.show', $this->slug))
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
