@if (filled($_blockname ?? null))
<!--// {{ $_blockname }} -->
@endif
{!! app(\Spatie\LaravelMarkdown\MarkdownRenderer::class)->toHtml((string) ($content ?? '')) !!}
@if (filled($_blockname ?? null))
<!-- {{ $_blockname }} //-->
@endif
