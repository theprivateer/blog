<?php

namespace App\Models;

use Database\Factories\NoteFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Note extends Model implements BacksUpToFlatFile, Feedable
{
    /** @use HasFactory<NoteFactory> */
    use HasFactory;

    use HasSlug;
    use RendersBody;

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
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function toFeedItem(): FeedItem
    {
        $reply = '<p><a href="mailto:hello@philstephens.com?subject=Comment: '.$this->title.'">Email a comment</a></p>';

        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->render().$reply)
            ->updated($this->updated_at)
            ->link(route('notes.show', $this->slug))
            ->authorName('Phil Stephens')
            ->authorEmail('hello@philstephens.com');
    }

    public static function getFeedItems(): Collection
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
        return $this->getAttribute('created_at')->format('c').'.'.
            $this->getAttribute('slug').
            '.md';
    }
}
