<x-site-layout :metadata="$metadata">
    <h1>{{ $listingPage?->title ?? $metadata->title }}</h1>

    @if ($listingPage)
        {!! $listingPage->render() !!}
    @endif

    <section>
        @foreach($posts as $post)
            <article class="margin-end-xl">
                <h3 class="margin-end-0"><a href="{{ route('posts.show', $post->slug) }}">{{  $post->title }}</a></h3>
                <p class="text-muted"><em>Posted {{ $post->published_at->format('j F Y') }}
                @if($post->category)
                in <a href="{{  route('categories.show', $post->category) }}">{{ $post->category->title }}</a>
                @endif
                </em></p>
            </article>
        @endforeach

        {!! $posts->links() !!}
    </section>
</x-site-layout>
