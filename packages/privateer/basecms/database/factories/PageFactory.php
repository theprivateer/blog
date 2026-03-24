<?php

namespace Privateer\Basecms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Basecms\Models\Page;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'body' => fake()->paragraphs(3, true),
        ];
    }

    public function homepage(): static
    {
        return $this->state(fn (): array => [
            'is_homepage' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'draft' => true,
        ]);
    }
}
