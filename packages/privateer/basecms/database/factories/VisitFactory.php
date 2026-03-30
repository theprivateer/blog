<?php

namespace Privateer\Basecms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Basecms\Models\Visit;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    protected $model = Visit::class;

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
            'visitor_type' => null,
            'visitor_label' => null,
            'classification_source' => null,
        ];
    }
}
