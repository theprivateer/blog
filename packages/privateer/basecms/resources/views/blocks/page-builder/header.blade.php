<section>
    @if (filled(data_get($data, 'heading')))
        <h2>{{ data_get($data, 'heading') }}</h2>
    @endif

    {!! app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)->toHtml((string) data_get($data, 'content', '')) !!}
</section>
