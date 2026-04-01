<?php

namespace Privateer\Basecms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MetaDescriptionGenerator
{
    public function formDataFromRecord(Model $record): array
    {
        return $record->attributesToArray();
    }

    public function generate(Model $record, array $formData): string
    {
        $title = $this->normalizeText((string) data_get($formData, 'title', ''));
        $renderedBody = $this->renderBodyFromFormData($record, $formData);
        $content = $this->normalizeText(html_entity_decode(strip_tags($renderedBody), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if (blank($title) || blank($content)) {
            throw new MetaDescriptionGenerationException('A title and body are required to generate a meta description.');
        }

        $response = app(GenerateMetaDescriptionAgent::class)->prompt($this->buildPrompt($title, $content));
        $description = $this->normalizeText((string) ($response['description'] ?? ''));

        if (blank($description)) {
            throw new MetaDescriptionGenerationException('The AI provider did not return a usable meta description.');
        }

        return $description;
    }

    protected function renderBodyFromFormData(Model $record, array $formData): string
    {
        $draftRecord = $record->newInstance([], exists: false);

        $draftRecord->forceFill(Arr::except($formData, ['metadata']));

        if (! method_exists($draftRecord, 'render')) {
            throw new MetaDescriptionGenerationException('This record type does not support rendered body generation.');
        }

        return (string) $draftRecord->render();
    }

    protected function buildPrompt(string $title, string $content): string
    {
        return implode("\n\n", [
            'Create an SEO meta description for the following content.',
            "Title:\n{$title}",
            "Rendered body content:\n{$content}",
            'Return only the description.',
        ]);
    }

    protected function normalizeText(string $text): string
    {
        return Str::of($text)
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->toString();
    }
}
