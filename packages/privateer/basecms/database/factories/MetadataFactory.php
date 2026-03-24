<?php

namespace Privateer\Basecms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Basecms\Models\Metadata;

/**
 * @extends Factory<Metadata>
 */
class MetadataFactory extends Factory
{
    protected $model = Metadata::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}
