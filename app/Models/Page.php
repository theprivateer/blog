<?php

namespace App\Models;

use App\Events\PostSaved;
use App\Events\PostDeleted;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Page extends Model implements BacksUpToFlatFile
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory;
    use RendersBody;
    use HasSlug;

    protected $fillable = ['title', 'body', 'is_homepage', 'template', 'draft'];

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
        return 'pages';
    }

    public function getFrontmatterColumns(): array
    {
        return [
            'title',
            'template',
            'draft',
            'created_at',
            'updated_at',
        ];
    }

    public function getFlatFileFilename(): string
    {
        return $this->getAttribute('slug') . '.md';
    }
}
