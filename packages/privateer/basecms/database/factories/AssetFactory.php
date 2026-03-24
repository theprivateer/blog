<?php

namespace Privateer\Basecms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Basecms\Models\Asset;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disk' => 's3',
            'path' => 'attachments/'.fake()->uuid().'.png',
            'directory' => 'attachments',
            'filename' => fake()->slug().'.png',
            'mime_type' => 'image/png',
            'size' => fake()->numberBetween(10_000, 200_000),
            'visibility' => 'public',
            'url' => fake()->url(),
            'field' => 'body',
            'uploaded_by' => null,
        ];
    }
}
