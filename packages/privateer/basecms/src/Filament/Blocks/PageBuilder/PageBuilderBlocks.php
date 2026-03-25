<?php

namespace Privateer\Basecms\Filament\Blocks\PageBuilder;

use Filament\Forms\Components\Builder\Block;
use Illuminate\Support\Str;

class PageBuilderBlocks
{
    public static function configured(): array
    {
        return collect(config('basecms.pages.builder.blocks', []))
            ->filter(fn (mixed $blockClass): bool => is_string($blockClass) && is_subclass_of($blockClass, PageBuilderBlock::class))
            ->values()
            ->all();
    }

    public static function resolve(string $type): ?PageBuilderBlock
    {
        $blockClass = collect(self::configured())
            ->first(fn (string $configuredBlockClass): bool => self::nameForClass($configuredBlockClass) === $type);

        if (! is_string($blockClass)) {
            return null;
        }

        /** @var PageBuilderBlock $block */
        $block = app($blockClass);

        return $block;
    }

    public static function toFilamentBlocks(): array
    {
        return collect(self::configured())
            ->map(function (string $blockClass): Block {
                /** @var PageBuilderBlock $block */
                $block = app($blockClass);

                return Block::make(self::nameForClass($blockClass))
                    ->label(self::labelForClass($blockClass))
                    ->schema($block->schema());
            })
            ->all();
    }

    public static function nameForClass(string $blockClass): string
    {
        $baseName = (string) Str::of(class_basename($blockClass))
            ->beforeLast('Block');

        return (string) Str::of($baseName)->kebab();
    }

    public static function labelForClass(string $blockClass): string
    {
        $baseName = (string) Str::of(class_basename($blockClass))
            ->beforeLast('Block');

        return (string) Str::of($baseName)->headline();
    }
}
