<?php

namespace App\Models;

use App\Events\PostSaved;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use App\Events\PostDeleted;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends Model implements Feedable, BacksUpToFlatFile
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;
    use RendersBody;
    use HasSlug;

    protected $fillable = ['title', 'body', 'link'];

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
            ->updated($this->updated_at)
            ->link(route('notes.show', $this->slug))
            ->authorName('Phil Stephens')
            ->authorEmail('hello@philstephens.com');
    }

    public static function getFeedItems()
    {
        return Note::latest()
            ->limit(20)
            ->get();
    }

    public function getDiskName(): string
    {
        return 'notes';
    }

    public function getFrontmatterColumns(): array
    {
        return [
            'title',
            'link',
            'created_at',
            'updated_at',
        ];
    }

    public function getFlatFileFilename(): string
    {
        return $this->getAttribute('created_at')->format('c') . '.' .
            $this->getAttribute('slug') .
            '.md';
    }
}
