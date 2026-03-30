<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;
use Privateer\Basecms\Models\Visit;
use Privateer\Basecms\Services\VisitClassifier;

class ReclassifyVisits extends Command
{
    protected $signature = 'basecms:reclassify-visits';

    protected $description = 'Reclassify all stored visits using the current visit classifier.';

    public function __construct(private readonly VisitClassifier $visitClassifier)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $totalVisits = Visit::query()->count();

        if ($totalVisits === 0) {
            $this->info('No visits found. Nothing to reclassify.');

            return self::SUCCESS;
        }

        $this->line("Reclassifying {$totalVisits} visits...");

        $progressBar = $this->output->createProgressBar($totalVisits);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        $processedVisits = 0;

        Visit::query()
            ->orderBy('id')
            ->chunkById(250, function ($visits) use (&$processedVisits, $progressBar): void {
                foreach ($visits as $visit) {
                    $visit->forceFill(
                        $this->visitClassifier->classify($visit->user_agent)
                    )->saveQuietly();

                    $processedVisits++;
                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Visit reclassification completed successfully.');
        $this->line("Processed {$processedVisits} visits.");

        return self::SUCCESS;
    }
}
