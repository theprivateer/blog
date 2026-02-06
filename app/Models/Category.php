<?php

namespace App\Models;

use App\Events\PostSaved;
use App\Events\PostDeleted;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model implements BacksUpToFlatFile
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
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

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function metadata(): MorphOne
    {
        return $this->morphOne(Metadata::class, 'parent');
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
        return $this->getAttribute('slug') . '.md';
    }
}
