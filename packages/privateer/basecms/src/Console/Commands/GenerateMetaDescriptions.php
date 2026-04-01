<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Privateer\Basecms\Services\MetaDescriptionGenerationException;
use Privateer\Basecms\Services\MetaDescriptionGenerator;
use Throwable;

class GenerateMetaDescriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'basecms:generate-meta-descriptions {model} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk-generate AI meta descriptions for Base CMS posts or pages.';

    public function __construct(private readonly MetaDescriptionGenerator $generator)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! config('basecms.ai.generate_meta_descriptions.enabled', false)) {
            $this->warn('AI meta description generation is disabled. Set basecms.ai.generate_meta_descriptions.enabled to true to run this command.');

            return self::SUCCESS;
        }

        $modelKey = strtolower((string) $this->argument('model'));
        $modelClass = $this->resolveModelClass($modelKey);

        if ($modelClass === null) {
            $this->error("Unsupported model [{$modelKey}]. Supported models are: post, page.");

            return self::FAILURE;
        }

        $query = $this->queryForModel($modelClass, (bool) $this->option('force'));
        $records = $query->get();
        $force = (bool) $this->option('force');
        $count = $records->count();

        if ($count === 0) {
            $this->info("No {$modelKey} records found to process.");

            return self::SUCCESS;
        }

        $this->line("Generating meta descriptions for {$count} {$modelKey} records".($force ? ' with --force' : '').'...');

        $processed = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($records as $record) {
            $processed++;

            try {
                $description = $this->generator->generate($record, $this->generator->formDataFromRecord($record));
            } catch (MetaDescriptionGenerationException $exception) {
                $skipped++;
                $this->warn("Skipping {$modelKey} [{$record->getKey()}] {$this->recordLabel($record)}: {$exception->getMessage()}");

                continue;
            } catch (Throwable $exception) {
                $failed++;
                report($exception);
                $this->warn("Failed {$modelKey} [{$record->getKey()}] {$this->recordLabel($record)}: {$exception->getMessage()}");

                continue;
            }

            if ($record->metadata()->exists()) {
                $record->metadata()->update([
                    'description' => $description,
                ]);
            } else {
                $record->metadata()->create([
                    'description' => $description,
                ]);
            }

            $updated++;
        }

        $this->info("Processed {$processed} {$modelKey} records.");
        $this->line("Updated {$updated} descriptions.");
        $this->line("Skipped {$skipped} records.");

        if ($failed > 0) {
            $this->line("Failed {$failed} records.");
        }

        return self::SUCCESS;
    }

    /**
     * @return class-string<Model>|null
     */
    protected function resolveModelClass(string $modelKey): ?string
    {
        $modelClass = match ($modelKey) {
            'post' => config('basecms.models.post'),
            'page' => config('basecms.models.page'),
            default => null,
        };

        if (! is_string($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return null;
        }

        return $modelClass;
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return Builder<Model>
     */
    protected function queryForModel(string $modelClass, bool $force): Builder
    {
        /** @var Builder<Model> $query */
        $query = $modelClass::query()->with('metadata');

        if ($force) {
            return $query;
        }

        return $query->whereDoesntHave('metadata')
            ->orWhereHas('metadata', function (Builder $query): void {
                $query->whereNull('description')
                    ->orWhere('description', '');
            });
    }

    protected function recordLabel(Model $record): string
    {
        $title = (string) ($record->getAttribute('title') ?? '');

        return $title !== '' ? "[{$title}]" : '';
    }
}
