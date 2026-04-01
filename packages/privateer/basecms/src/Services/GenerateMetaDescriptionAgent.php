<?php

namespace Privateer\Basecms\Services;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class GenerateMetaDescriptionAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'TEXT'
You write SEO meta descriptions for website content.

Return a single plain-text meta description that:
- accurately reflects the supplied content
- does not repeat or include the page title
- is concise and compelling without sounding promotional
- is suitable for a search result snippet
- is roughly 140 to 160 characters
- contains no quotation marks, markdown, labels, or filler
TEXT;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'description' => $schema->string()->min(120)->max(170)->required(),
        ];
    }
}
