<?php

namespace Privateer\Basecms\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Privateer\Basecms\Database\Factories\CategoryFactory;
use Privateer\Basecms\Events\PostDeleted;
use Privateer\Basecms\Events\PostSaved;
use Privateer\Basecms\Models\Concerns\BelongsToSite;
use Privateer\Basecms\Services\SiteManager;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model implements BacksUpToFlatFile
{
    use BelongsToSite;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasSlug;
    use RendersBody;

    protected $fillable = ['site_id', 'title', 'body'];

    /**
     * The event map for the model.
     *
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'deleted' => PostDeleted::class,
    ];

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
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
        $siteId = $this->getAttribute('site_id') ?: app(SiteManager::class)->required()->getKey();

        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->extraScope(fn (Builder $query): Builder => $query->where('site_id', $siteId))
            ->doNotGenerateSlugsOnUpdate();
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
