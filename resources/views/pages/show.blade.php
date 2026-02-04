<x-site-layout :metadata="$metadata">
    <section>
        <h1>{{ $page->title }}</h1>

		{!! $page->render() !!}
    </section>
</x-site-layout>
