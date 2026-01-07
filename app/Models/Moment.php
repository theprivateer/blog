<?php

namespace App\Models;

use App\Events\PostSaved;
use Spatie\Feed\FeedItem;
use App\Events\PostDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Feed\Feedable;

class Moment extends Model implements Feedable, BacksUpToFlatFile
{
    /** @use HasFactory<\Database\Factories\MomentFactory> */
    use HasFactory;
    use RendersBody;

    protected $fillable = ['body'];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];

    public function toFeedItem(): FeedItem
    {
        $reply = '<p><a href="mailto:hello@philstephens.com?subject=Comment: ' . $this->title . '">Email a comment</a></p>';

        return FeedItem::create()
            ->id($this->id)
            ->title(Str::of(strip_tags($this->render()))->words(10))
            ->summary($this->render() . $reply)
            ->updated($this->updated_at)
            ->link(route('moments.show', $this))
            ->authorName('Phil Stephens')
            ->authorEmail('hello@philstephens.com');
    }

    public static function getFeedItems()
    {
        return Moment::latest()
            ->limit(20)
            ->get();
    }

    public function getDiskName(): string
    {
        return 'moments';
    }

    public function getFrontmatterColumns(): array
    {
        return [
            'created_at',
            'updated_at',
        ];
    }

    public function getFlatFileFilename(): string
    {
        return $this->getAttribute('created_at')->format('c') . '.md';
    }
}
