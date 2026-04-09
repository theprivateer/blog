<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Privateer\Basecms\Models\Site;
use Tests\TestCase;

class InstallTest extends TestCase
{
    use RefreshDatabase;

    protected string $configPath;

    protected string $userModelPath;

    protected string $originalConfig;

    protected string $originalUserModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configPath = config_path('basecms.php');
        $this->userModelPath = app_path('Models/User.php');
        $this->originalConfig = File::get($this->configPath);
        $this->originalUserModel = File::get($this->userModelPath);
    }

    protected function tearDown(): void
    {
        File::put($this->configPath, $this->originalConfig);
        File::put($this->userModelPath, $this->originalUserModel);

        parent::tearDown();
    }

    public function test_install_command_creates_the_first_admin_user_and_site(): void
    {
        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsQuestion('Admin password', 'secret-passphrase')
            ->expectsQuestion('Site name', 'Phil Stephens')
            ->expectsQuestion('Site key', 'phil-stephens')
            ->expectsOutputToContain('Admin user created for phil@example.com.')
            ->expectsOutputToContain('Admin panel: /admin')
            ->assertSuccessful();

        $user = User::query()->where('email', 'phil@example.com')->first();
        $site = Site::query()->where('key', 'phil-stephens')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($site);
        $this->assertSame('Phil Stephens', $user->name);
        $this->assertSame('Phil Stephens', $site->name);
        $this->assertTrue(Hash::check('secret-passphrase', $user->password));
    }

    public function test_install_command_allows_overriding_the_suggested_site_key(): void
    {
        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsQuestion('Admin password', 'secret-passphrase')
            ->expectsQuestion('Site name', 'Phil Stephens')
            ->expectsQuestion('Site key', 'portfolio')
            ->assertSuccessful();

        $this->assertDatabaseHas('sites', [
            'name' => 'Phil Stephens',
            'key' => 'portfolio',
        ]);
    }

    public function test_install_command_scaffolds_the_user_model_when_missing(): void
    {
        File::delete($this->userModelPath);

        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsQuestion('Admin password', 'secret-passphrase')
            ->expectsQuestion('Site name', 'Phil Stephens')
            ->expectsQuestion('Site key', 'phil-stephens')
            ->expectsOutputToContain('Admin user created for phil@example.com.')
            ->expectsOutputToContain('Admin panel: /admin')
            ->expectsOutputToContain('Scaffolded app/Models/User.php.')
            ->assertSuccessful();

        $this->assertFileExists($this->userModelPath);
        $this->assertStringContainsString('class User extends Authenticatable implements FilamentUser, HasDefaultTenant, HasTenants', File::get($this->userModelPath));
    }

    public function test_install_command_does_not_overwrite_an_existing_user_model(): void
    {
        $customUserModel = str_replace('return true;', 'return $panel->getId() === \'admin\';', $this->originalUserModel);

        File::put($this->userModelPath, $customUserModel);

        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsQuestion('Admin password', 'secret-passphrase')
            ->expectsQuestion('Site name', 'Phil Stephens')
            ->expectsQuestion('Site key', 'phil-stephens')
            ->assertSuccessful();

        $this->assertSame($customUserModel, File::get($this->userModelPath));
    }

    public function test_install_command_updates_the_user_binding_only_when_it_is_null(): void
    {
        File::put(
            $this->configPath,
            str_replace("'user' => User::class,", "'user' => null,", $this->originalConfig),
        );

        config()->set('basecms.models.user', null);

        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsQuestion('Admin password', 'secret-passphrase')
            ->expectsQuestion('Site name', 'Phil Stephens')
            ->expectsQuestion('Site key', 'phil-stephens')
            ->expectsOutputToContain('Updated config/basecms.php to bind App\Models\User.')
            ->assertSuccessful();

        $this->assertStringContainsString("'user' => \\App\\Models\\User::class,", File::get($this->configPath));
        $this->assertSame(1, substr_count(File::get($this->configPath), "'user' => \\App\\Models\\User::class,"));
    }

    public function test_install_command_rejects_duplicate_admin_email_addresses(): void
    {
        User::factory()->create(['email' => 'phil@example.com']);

        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsOutputToContain('That email address is already in use.')
            ->assertExitCode(1);
    }

    public function test_install_command_rejects_duplicate_site_keys(): void
    {
        Site::factory()->create(['key' => 'phil-stephens']);

        $this->artisan('basecms:install')
            ->expectsQuestion('Admin name', 'Phil Stephens')
            ->expectsQuestion('Admin email', 'phil@example.com')
            ->expectsQuestion('Admin password', 'secret-passphrase')
            ->expectsQuestion('Site name', 'Phil Stephens')
            ->expectsQuestion('Site key', 'phil-stephens')
            ->expectsOutputToContain('That site key is already in use.')
            ->assertExitCode(1);
    }
}
