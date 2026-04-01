<?php

namespace Privateer\Basecms\Filament;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Privateer\Basecms\Services\MetaDescriptionGenerationException;
use Privateer\Basecms\Services\MetaDescriptionGenerator;
use Throwable;

class GenerateMetaDescriptionAction
{
    public static function make(): Action
    {
        return Action::make('generateMetaDescription')
            ->label('Generate Meta Description')
            ->icon(Heroicon::OutlinedSparkles)
            ->action(function (EditRecord $livewire, MetaDescriptionGenerator $generator): void {
                try {
                    $description = $generator->generate($livewire->getRecord(), $livewire->data ?? []);
                } catch (MetaDescriptionGenerationException $exception) {
                    Notification::make()
                        ->warning()
                        ->title('Meta description was not generated')
                        ->body($exception->getMessage())
                        ->send();

                    return;
                } catch (Throwable $exception) {
                    report($exception);

                    Notification::make()
                        ->danger()
                        ->title('Meta description generation failed')
                        ->body('The AI provider could not generate a meta description right now.')
                        ->send();

                    return;
                }

                $data = $livewire->data ?? [];

                data_set($data, 'metadata.description', $description);

                $livewire->data = $data;

                Notification::make()
                    ->success()
                    ->title('Meta description generated')
                    ->send();
            });
    }
}
