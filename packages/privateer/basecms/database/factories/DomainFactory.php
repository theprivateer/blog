<?php

namespace Privateer\Basecms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Privateer\Basecms\Models\Domain;
use Privateer\Basecms\Models\Site;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    protected $model = Domain::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'domain' => fake()->unique()->domainName(),
            'is_primary' => true,
        ];
    }
}
