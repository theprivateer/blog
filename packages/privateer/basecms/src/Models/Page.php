<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Privateer\Basecms\Database\Factories\PageFactory;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model implements BacksUpToFlatFile
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    use HasSlug;
    use RendersBody;

    protected $fillable = ['title', 'body', 'use_builder', 'blocks', 'is_homepage', 'template', 'draft'];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'use_builder' => 'boolean',
            'blocks' => 'array',
            'is_homepage' => 'boolean',
            'draft' => 'boolean',
        ];
    }

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
        return 'pages';
    }

    public function getFrontmatterColumns(): array
    {
        return [
            'title',
            'use_builder',
            'blocks',
            'template',
            'draft',
            'created_at',
            'updated_at',
        ];
    }

    public function getFlatFileFilename(): string
    {
        return $this->getAttribute('slug').'.md';
    }
}
