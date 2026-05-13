@if (filled($_blockname ?? null))
<!--// {{ $_blockname }} -->
@endif
<section>
    @if (filled($heading ?? null))
        <h2>{{ $heading }}</h2>
    @endif

    {!! app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)->toHtml((string) ($content ?? '')) !!}
</section>
@if (filled($_blockname ?? null))
<!-- {{ $_blockname }} //-->
@endif
