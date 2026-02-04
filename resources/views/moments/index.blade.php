<x-site-layout :metadata="$metadata">
    <h1>{{ $page->title }}</h1>

    {!! $page->render() !!}

    <section class="grid-l margin-end-4xl">
        @foreach($moments as $moment)
        <article class="box padding-m item-half">
            <p class="text-muted">{{ $moment->created_at->format('l, j F Y') }}</p>

            {!! $moment->render() !!}
        </article>
        @endforeach
    </section>

    {!! $moments->links() !!}
</x-site-layout>
