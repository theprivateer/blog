<?php

namespace App\Models;

use App\Events\PostSaved;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use App\Events\PostDeleted;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model implements Feedable, BacksUpToFlatFile
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;
    use RendersBody;
    use HasSlug;

    protected $fillable = ['title', 'body', 'published_at'];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime:Y-m-d',
        ];
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function toFeedItem(): FeedItem
    {
        $reply = '<p><a href="mailto:hello@philstephens.com?subject=Comment: ' . $this->title . '">Email a comment</a></p>';

        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->render() . $reply)
            ->updated($this->published_at)
            ->link(route('posts.show', $this->slug))
            ->authorName('Phil Stephens')
            ->authorEmail('hello@philstephens.com');
    }

    public static function getFeedItems()
    {
        return Post::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function getDiskName(): string
    {
        return 'posts';
    }

    public function getFrontmatterColumns(): array
    {
        return [
            'title',
            'published_at',
            'created_at',
            'updated_at',
        ];
    }

    public function getFlatFileFilename(): string
    {
        $slugged =  $this->getAttribute('slug') . '.md';

        if ($this->getAttribute('published_at')) {
            return $this->getAttribute('published_at')->format('c') . '.' . $slugged;
        }

        return $slugged;
    }
}
