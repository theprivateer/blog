<section>
    @if (filled($heading ?? null))
        <h2>{{ $heading }}</h2>
    @endif

    {!! app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)->toHtml((string) ($content ?? '')) !!}
</section>
