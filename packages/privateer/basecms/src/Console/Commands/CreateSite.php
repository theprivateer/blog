<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Privateer\Basecms\Models\Site;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\text;

class CreateSite extends Command
{
    protected $signature = 'basecms:create-site';

    protected $description = 'Create a new site for a multi-site Base CMS app.';

    public function handle(): int
    {
        if (! config('basecms.multisite.enabled', false)) {
            $this->error('Multi-site is disabled. Enable basecms.multisite.enabled before creating another site.');

            return self::FAILURE;
        }

        $siteModelClass = $this->resolveSiteModelClass();

        if ($siteModelClass === null) {
            $this->error('Could not resolve the Base CMS site model from basecms.models.site.');

            return self::FAILURE;
        }

        intro('Create a new Base CMS site');

        $siteName = text(
            label: 'Site name',
            required: 'A site name is required.',
            validate: fn (string $value): ?string => blank(trim($value))
                ? 'A site name is required.'
                : null,
        );

        $siteKey = text(
            label: 'Site key',
            default: Str::slug($siteName),
            hint: 'Suggested from the site name. You can change it before saving.',
            required: 'A site key is required.',
            validate: fn (string $value): ?string => $this->validateSiteKey($value, $siteModelClass),
        );

        /** @var Site $site */
        $site = $siteModelClass::query()->create([
            'name' => trim($siteName),
            'key' => trim($siteKey),
        ]);

        $this->info("Site name: {$site->name}.");
        $this->info("Site key: {$site->key}.");

        return self::SUCCESS;
    }

    /**
     * @return class-string<Site>|null
     */
    protected function resolveSiteModelClass(): ?string
    {
        $siteModelClass = config('basecms.models.site', Site::class);

        if (! is_string($siteModelClass) || ! class_exists($siteModelClass)) {
            return null;
        }

        if (! is_a($siteModelClass, Site::class, true)) {
            return null;
        }

        return $siteModelClass;
    }

    protected function validateSiteKey(string $value, string $siteModelClass): ?string
    {
        $siteKey = trim($value);

        if ($siteKey === '') {
            return 'A site key is required.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $siteKey)) {
            return 'The site key must be lowercase letters, numbers, and dashes only.';
        }

        if ($siteModelClass::query()->where('key', $siteKey)->exists()) {
            return 'That site key is already in use.';
        }

        return null;
    }
}
