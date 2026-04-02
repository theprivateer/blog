<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Privateer\Basecms\Models\Page;
use Privateer\Basecms\Models\Post;
use Privateer\Basecms\Models\Visit;

class VisitSeeder extends Seeder
{
    public function run(): void
    {
        $weightedPaths = $this->weightedPaths();

        if ($weightedPaths === []) {
            $this->command?->warn('VisitSeeder skipped: no public content paths were found.');

            return;
        }

        $sessionIds = collect(range(1, random_int(40, 80)))
            ->map(fn (): string => Str::uuid()->toString())
            ->all();

        $visitsToCreate = random_int(200, 300);
        $missingPaths = $this->missingPaths();

        Visit::factory()
            ->count($visitsToCreate)
            ->make()
            ->each(function (Visit $visit) use ($sessionIds, $weightedPaths, $missingPaths): void {
                $windowStart = now()->subDays(7)->addSecond();
                $visitedAt = $windowStart->copy()->addSeconds(
                    fake()->numberBetween(0, $windowStart->diffInSeconds(now()))
                );
                $useMissingPath = $missingPaths !== [] && fake()->boolean(12);

                $path = $useMissingPath
                    ? Arr::random($missingPaths)
                    : Arr::random($weightedPaths);

                $visit->forceFill([
                    'path' => $path,
                    'session_id' => Arr::random($sessionIds),
                    'response_status' => $useMissingPath ? 404 : 200,
                    'created_at' => $visitedAt,
                    'updated_at' => $visitedAt,
                ])->save();
            });
    }

    /**
     * @return array<int, string>
     */
    protected function weightedPaths(): array
    {
        $pages = Page::query()
            ->where('draft', false)
            ->where('slug', '!=', 'home')
            ->pluck('slug');

        $posts = Post::query()
            ->published()
            ->pluck('slug')
            ->map(fn (string $slug): string => "blog/{$slug}");

        $notes = Note::query()
            ->pluck('slug')
            ->map(fn (string $slug): string => "notes/{$slug}");

        if ($pages->isEmpty() && $posts->isEmpty() && $notes->isEmpty()) {
            return [];
        }

        $basePaths = collect([
            '/',
            'blog',
            'notes',
        ])
            ->merge($pages)
            ->merge($posts)
            ->merge($notes)
            ->unique()
            ->values();

        if ($basePaths->isEmpty()) {
            return [];
        }

        return $this->applyWeights($basePaths);
    }

    /**
     * @param  Collection<int, string>  $paths
     * @return array<int, string>
     */
    protected function applyWeights(Collection $paths): array
    {
        $weightedPaths = [];

        foreach ($paths as $index => $path) {
            $weight = match (true) {
                $path === '/' => 18,
                in_array($path, ['blog', 'notes'], true) => 14,
                $index < 8 => 10,
                $index < 18 => 6,
                default => 3,
            };

            array_push($weightedPaths, ...array_fill(0, $weight, $path));
        }

        return $weightedPaths;
    }

    /**
     * @return array<int, string>
     */
    protected function missingPaths(): array
    {
        return collect(range(1, random_int(10, 18)))
            ->map(function (): string {
                $prefix = Arr::random([
                    'blog',
                    'notes',
                    'page',
                    'category',
                    'archive',
                ]);

                return "{$prefix}/missing-".fake()->unique()->slug(2);
            })
            ->unique()
            ->values()
            ->all();
    }
}
