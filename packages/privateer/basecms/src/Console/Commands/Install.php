<?php

namespace Privateer\Basecms\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Privateer\Basecms\Models\Site;

use function Laravel\Prompts\intro;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class Install extends Command
{
    protected $signature = 'basecms:install';

    protected $description = 'Bootstrap Base CMS with the first admin user and site.';

    public function handle(): int
    {
        $userScaffolded = $this->ensureUserModelExists();
        $configUpdated = $this->ensureConfiguredUserModelBinding();

        $userModelClass = $this->resolveUserModelClass();
        $siteModelClass = $this->resolveSiteModelClass();

        if ($userModelClass === null) {
            $this->error('Could not resolve the auth user model from auth.providers.users.model.');

            return self::FAILURE;
        }

        if ($siteModelClass === null) {
            $this->error('Could not resolve the Base CMS site model from basecms.models.site.');

            return self::FAILURE;
        }

        intro('Base CMS first-time install');

        $adminName = text(
            label: 'Admin name',
            required: 'An admin name is required.',
            validate: fn (string $value): ?string => blank(trim($value))
                ? 'An admin name is required.'
                : null,
        );

        $adminEmail = text(
            label: 'Admin email',
            placeholder: 'you@example.com',
            required: 'An admin email is required.',
            validate: fn (string $value): ?string => $this->validateAdminEmail($value, $userModelClass),
        );

        $adminPassword = password(
            label: 'Admin password',
            required: 'An admin password is required.',
            validate: fn (string $value): ?string => $this->validateAdminPassword($value),
        );

        $siteName = text(
            label: 'Site name',
            required: 'A site name is required.',
            validate: fn (string $value): ?string => blank(trim($value))
                ? 'A site name is required.'
                : null,
        );

        $suggestedSiteKey = Str::slug($siteName);

        $siteKey = text(
            label: 'Site key',
            default: $suggestedSiteKey,
            hint: 'Suggested from the site name. You can change it before saving.',
            required: 'A site key is required.',
            validate: fn (string $value): ?string => $this->validateSiteKey($value, $siteModelClass),
        );

        [$user, $site] = DB::transaction(function () use ($userModelClass, $siteModelClass, $adminName, $adminEmail, $adminPassword, $siteName, $siteKey): array {
            /** @var Model $user */
            $user = $userModelClass::query()->create([
                'name' => trim($adminName),
                'email' => strtolower(trim($adminEmail)),
                'password' => Hash::make($adminPassword),
            ]);

            /** @var Site $site */
            $site = $siteModelClass::query()->create([
                'name' => trim($siteName),
                'key' => trim($siteKey),
            ]);

            return [$user, $site];
        });

        $messages = [
            "Admin user created for {$user->getAttribute('email')}.",
            "Site name: {$site->name}.",
            "Site key: {$site->key}.",
            'Admin panel: /'.trim((string) config('basecms.panel.path', 'admin'), '/'),
        ];

        if ($userScaffolded) {
            $messages[] = 'Scaffolded app/Models/User.php.';
        }

        if ($configUpdated) {
            $messages[] = 'Updated config/basecms.php to bind App\Models\User.';
        }

        foreach ($messages as $message) {
            $this->info($message);
        }

        return self::SUCCESS;
    }

    protected function ensureUserModelExists(): bool
    {
        $userModelPath = app_path('Models/User.php');

        if (File::exists($userModelPath)) {
            return false;
        }

        $stubPath = __DIR__.'/../../../stubs/app/Models/User.php.stub';

        File::ensureDirectoryExists(dirname($userModelPath));
        File::copy($stubPath, $userModelPath);

        return true;
    }

    protected function ensureConfiguredUserModelBinding(): bool
    {
        if (config('basecms.models.user') !== null) {
            return false;
        }

        $configPath = config_path('basecms.php');

        if (! File::exists($configPath)) {
            return false;
        }

        $originalContents = File::get($configPath);
        $updatedContents = preg_replace(
            "/'user'\\s*=>\\s*null,/",
            "'user' => \\App\\Models\\User::class,",
            $originalContents,
            1,
            $count,
        );

        if ($count !== 1 || ! is_string($updatedContents)) {
            return false;
        }

        File::put($configPath, $updatedContents);
        config()->set('basecms.models.user', User::class);

        return true;
    }

    /**
     * @return class-string<Model>|null
     */
    protected function resolveUserModelClass(): ?string
    {
        $userModelClass = config('auth.providers.users.model');

        if (! is_string($userModelClass) || ! class_exists($userModelClass)) {
            return null;
        }

        if (! is_subclass_of($userModelClass, Model::class)) {
            return null;
        }

        return $userModelClass;
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

    protected function validateAdminEmail(string $value, string $userModelClass): ?string
    {
        $email = strtolower(trim($value));

        if ($email === '') {
            return 'An admin email is required.';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address.';
        }

        if ($userModelClass::query()->where('email', $email)->exists()) {
            return 'That email address is already in use.';
        }

        return null;
    }

    protected function validateAdminPassword(string $value): ?string
    {
        if ($value === '') {
            return 'An admin password is required.';
        }

        if (Str::length($value) < 8) {
            return 'The admin password must be at least 8 characters.';
        }

        return null;
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
