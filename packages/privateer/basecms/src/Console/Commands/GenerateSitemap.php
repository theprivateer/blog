<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'basecms:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap using the configured sitemap service.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sitemapService = config('basecms.services.sitemap');

        if (! is_string($sitemapService) || ! class_exists($sitemapService)) {
            $this->warn('No sitemap service is configured. Nothing to generate.');

            return self::SUCCESS;
        }

        $service = app($sitemapService);

        if (! method_exists($service, 'generate')) {
            $this->warn("The configured sitemap service [{$sitemapService}] does not define a generate method.");

            return self::SUCCESS;
        }

        $service->generate();

        $this->info('Sitemap generated successfully.');

        return self::SUCCESS;
    }
}
