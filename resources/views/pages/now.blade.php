<x-site-layout :metadata="$metadata">
    <section>
        <h1>{{ $page->title }}</h1>

		{!! $page->render() !!}

        <aside class="callout primary">
            <p>Last updated: {{ $page->updated_at->format('l, j F Y') }}</p>

            <p>This page is inspired by <a href="https://nownownow.com/about">Derek Sivers' now page movement</a>.</p>
        </aside>
    </section>
</x-site-layout>
