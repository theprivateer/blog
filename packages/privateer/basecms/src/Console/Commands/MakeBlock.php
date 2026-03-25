<?php

namespace Privateer\Basecms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeBlock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'basecms:make-block {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Base CMS page builder block class and matching Blade view.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseName = $this->normalizeBaseName((string) $this->argument('name'));

        if ($baseName === '') {
            $this->error('A valid block name is required.');

            return self::FAILURE;
        }

        $className = $baseName.'Block';
        $viewName = (string) Str::of($baseName)->kebab();
        $classPath = app_path("Filament/Blocks/{$className}.php");
        $viewPath = resource_path("views/blocks/page-builder/{$viewName}.blade.php");
        $configPath = config_path('basecms.php');

        if (File::exists($classPath)) {
            $this->error("Block class already exists at [{$classPath}].");

            return self::FAILURE;
        }

        if (File::exists($viewPath)) {
            $this->error("Block view already exists at [{$viewPath}].");

            return self::FAILURE;
        }

        if (! File::exists($configPath)) {
            $this->error("Could not find Base CMS config at [{$configPath}].");

            return self::FAILURE;
        }

        $updatedConfig = $this->updatedConfigContents(
            File::get($configPath),
            "\\App\\Filament\\Blocks\\{$className}::class",
        );

        if ($updatedConfig === null) {
            $this->error('Could not safely update config/basecms.php. Please ensure basecms.pages.builder.blocks exists.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($classPath));
        File::ensureDirectoryExists(dirname($viewPath));

        File::put($classPath, $this->classStub($className, $viewName));
        File::put($viewPath, $this->viewStub());
        File::put($configPath, $updatedConfig);

        $this->info("Created block class [{$classPath}].");
        $this->info("Created block view [{$viewPath}].");
        $this->info("Registered [App\\Filament\\Blocks\\{$className}] in config/basecms.php.");

        return self::SUCCESS;
    }

    protected function normalizeBaseName(string $name): string
    {
        $normalized = (string) Str::of($name)
            ->replace(['/', '\\', '-', '_'], ' ')
            ->squish()
            ->studly();

        if (Str::endsWith($normalized, 'Block')) {
            $normalized = (string) Str::of($normalized)->beforeLast('Block');
        }

        return $normalized;
    }

    protected function updatedConfigContents(string $configContents, string $classReference): ?string
    {
        if (str_contains($configContents, $classReference)) {
            return $configContents;
        }

        $updatedContents = preg_replace_callback(
            "/('blocks'\\s*=>\\s*\\[)(.*?)(\\n\\s*\\],)/s",
            fn (array $matches): string => $matches[1].$matches[2]."\n                {$classReference},".$matches[3],
            $configContents,
            1,
            $count,
        );

        if ($count !== 1 || ! is_string($updatedContents)) {
            return null;
        }

        return $updatedContents;
    }

    protected function classStub(string $className, string $viewName): string
    {
        return <<<PHP
<?php

namespace App\\Filament\\Blocks;

use Filament\\Forms\\Components\\TextInput;
use Privateer\\Basecms\\Filament\\Blocks\\PageBuilder\\PageBuilderBlock;

class {$className} implements PageBuilderBlock
{
    public function schema(): array
    {
        return [
            TextInput::make('content')
                ->label('Content')
                ->columnSpanFull(),
        ];
    }

    public function view(): string
    {
        return 'blocks.page-builder.{$viewName}';
    }
}
PHP;
    }

    protected function viewStub(): string
    {
        return <<<'BLADE'
<section>
    @if (filled($content ?? null))
        <p>{{ $content }}</p>
    @endif
</section>
BLADE;
    }
}
