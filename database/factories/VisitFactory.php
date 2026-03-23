<?php

namespace Database\Factories;

use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => '/'.fake()->slug(2),
            'method' => 'GET',
            'ip_address' => fake()->ipv4(),
            'session_id' => fake()->sha256(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
