<x-site-layout :metadata="$metadata">
    <h1>Category: {{ $category->title }}</h1>

    {!! $category->render() !!}

    <section>
        @foreach($posts as $post)
            <article class="margin-end-xl">
                <h3 class="margin-end-0"><a href="{{ route('posts.show', $post->slug) }}">{{  $post->title }}</a></h3>
                <p class="text-muted"><em>Posted {{ $post->published_at->format('j F Y') }}</em></p>
            </article>
        @endforeach

        {!! $posts->links() !!}
    </section>
</x-site-layout>
