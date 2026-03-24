<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Privateer\Basecms\Database\Factories\CategoryFactory;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model implements BacksUpToFlatFile
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasSlug;
    use RendersBody;

    protected $fillable = ['title', 'body'];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function metadata(): MorphOne
    {
        return $this->morphOne((string) config('basecms.models.metadata', Metadata::class), 'parent');
    }

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

    public function getDiskName(): string
    {
        return 'categories';
    }

    public function getFrontmatterColumns(): array
    {
        return [
            'id',
            'title',
            'created_at',
            'updated_at',
        ];
    }

    public function getFlatFileFilename(): string
    {
        return $this->getAttribute('slug').'.md';
    }
}
