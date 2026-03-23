<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
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
