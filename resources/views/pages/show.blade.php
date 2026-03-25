<x-site-layout :metadata="$metadata">
    <section>
        <h1>{{ $page->title }}</h1>

        @php($renderedPage = $page->render())

        @if (is_string($renderedPage))
            {!! $renderedPage !!}
        @endif
    </section>
</x-site-layout>
