<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;
use Privateer\Basecms\Console\Commands\Concerns\InteractsWithSelectedSite;
use Privateer\Basecms\Models\Site;
use Privateer\Basecms\Services\StaticSiteGenerator;

class GenerateStaticSite extends Command
{
    use InteractsWithSelectedSite;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'basecms:generate-static {--site=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a static export of the rendered website.';

    public function __construct(private readonly StaticSiteGenerator $generator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('basecms.static_site.enabled', false)) {
            $this->warn('Static site generation is disabled. Set basecms.static_site.enabled to true to export the site.');

            return self::SUCCESS;
        }

        $site = $this->resolveSelectedSite();

        if (! $site instanceof Site) {
            return self::FAILURE;
        }

        $this->line('Selected site: '.$this->describeSelectedSite($site));

        $routes = $this->generator->routes($site);
        $this->line('Generating static site...');
        $this->line('Resolved '.count($routes).' routes for export.');

        $progressBar = $this->output->createProgressBar(count($routes));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%%');
        $progressBar->start();

        $result = $this->generator->generate(function () use ($progressBar): void {
            $progressBar->advance();
        }, $site);

        $progressBar->finish();
        $this->newLine(2);

        foreach ($result['warnings'] as $warning) {
            $this->warn($warning);
        }

        $this->info("Static site generated successfully at [{$result['output_path']}].");
        $this->line("Exported {$result['exported_count']} routes.");

        if ($result['skipped_count'] > 0) {
            $this->line("Skipped {$result['skipped_count']} routes.");
        }

        return self::SUCCESS;
    }
}
