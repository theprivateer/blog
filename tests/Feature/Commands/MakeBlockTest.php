<?php

namespace Tests\Feature\Commands;

use App\Filament\Blocks\PromoHeroBlock;
use Illuminate\Support\Facades\File;
use Privateer\Basecms\Filament\Blocks\PageBuilder\PageBuilderBlock;
use Tests\TestCase;

class MakeBlockTest extends TestCase
{
    protected string $configPath;

    protected string $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configPath = config_path('basecms.php');
        $this->originalConfig = File::get($this->configPath);
    }

    protected function tearDown(): void
    {
        File::put($this->configPath, $this->originalConfig);

        $this->cleanupGeneratedBlock('PromoHero');
        $this->cleanupGeneratedBlock('FeatureBanner');

        parent::tearDown();
    }

    public function test_make_block_generates_class_view_and_config_registration(): void
    {
        $this->artisan('basecms:make-block', ['name' => 'Promo Hero'])
            ->assertSuccessful();

        $classPath = app_path('Filament/Blocks/PromoHeroBlock.php');
        $viewPath = resource_path('views/blocks/page-builder/promo-hero.blade.php');
        $classContents = File::get($classPath);

        $this->assertFileExists($classPath);
        $this->assertFileExists($viewPath);
        $this->assertStringContainsString('namespace App\\Filament\\Blocks;', $classContents);
        $this->assertStringContainsString('class PromoHeroBlock implements PageBuilderBlock', $classContents);
        $this->assertStringContainsString("return 'blocks.page-builder.promo-hero';", $classContents);
        $this->assertStringContainsString("TextInput::make('content')", $classContents);
        $this->assertStringContainsString('<p>{{ $content }}</p>', File::get($viewPath));
        $this->assertStringContainsString('\\App\\Filament\\Blocks\\PromoHeroBlock::class', File::get($this->configPath));

        $block = app(PromoHeroBlock::class);

        $this->assertInstanceOf(PageBuilderBlock::class, $block);
        $this->assertSame('blocks.page-builder.promo-hero', $block->view());
    }

    public function test_make_block_normalizes_names_with_block_suffix(): void
    {
        $this->artisan('basecms:make-block', ['name' => 'FeatureBannerBlock'])
            ->assertSuccessful();

        $this->assertFileExists(app_path('Filament/Blocks/FeatureBannerBlock.php'));
        $this->assertFileDoesNotExist(app_path('Filament/Blocks/FeatureBannerBlockBlock.php'));
        $this->assertFileExists(resource_path('views/blocks/page-builder/feature-banner.blade.php'));
        $this->assertStringContainsString('\\App\\Filament\\Blocks\\FeatureBannerBlock::class', File::get($this->configPath));
    }

    public function test_make_block_fails_cleanly_when_block_already_exists(): void
    {
        $this->artisan('basecms:make-block', ['name' => 'Promo Hero'])
            ->assertSuccessful();

        $originalClassContents = File::get(app_path('Filament/Blocks/PromoHeroBlock.php'));

        $this->artisan('basecms:make-block', ['name' => 'Promo Hero'])
            ->assertExitCode(1);

        $this->assertSame($originalClassContents, File::get(app_path('Filament/Blocks/PromoHeroBlock.php')));
        $this->assertSame(1, substr_count(File::get($this->configPath), '\\App\\Filament\\Blocks\\PromoHeroBlock::class'));
    }

    protected function cleanupGeneratedBlock(string $baseName): void
    {
        $viewName = (string) str($baseName)->kebab();

        File::delete(app_path("Filament/Blocks/{$baseName}Block.php"));
        File::delete(resource_path("views/blocks/page-builder/{$viewName}.blade.php"));

        $this->deleteDirectoryIfEmpty(app_path('Filament/Blocks'));
        $this->deleteDirectoryIfEmpty(resource_path('views/blocks/page-builder'));
        $this->deleteDirectoryIfEmpty(resource_path('views/blocks'));
    }

    protected function deleteDirectoryIfEmpty(string $path): void
    {
        if (! File::isDirectory($path)) {
            return;
        }

        if (count(File::files($path)) === 0 && count(File::directories($path)) === 0) {
            File::deleteDirectory($path);
        }
    }
}
