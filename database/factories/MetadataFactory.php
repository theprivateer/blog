<?php

namespace Database\Factories;

use App\Models\Metadata;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Metadata>
 */
class MetadataFactory extends Factory
{
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
