<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
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
            'intro' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'published_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'published_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => [
            'published_at' => null,
        ]);
    }

    public function future(): static
    {
        return $this->state(fn (): array => [
            'published_at' => fake()->dateTimeBetween('+1 day', '+1 year'),
        ]);
    }
}
