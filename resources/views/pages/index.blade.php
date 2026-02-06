<x-site-layout :metadata="$metadata">
    <section>
		{!! $page->render() !!}
    </section>
    <div class="space-y-8 relative mt-16">
        <h2 class="text-2xl font-bold">Recent Posts</h2>

        @foreach($posts as $post)
            <article class="margin-end-xl">
                <h3 class="margin-end-0"><a href="{{ route('posts.show', $post->slug) }}">{{  $post->title }}</a></h3>
                <p class="text-muted">
                    <em>Posted {{ $post->published_at->format('l, j F Y') }}
                    @if($post->category)
                    in <a href="{{  route('categories.show', $post->category) }}">{{ $post->category->title }}</a>
                    @endif
                    </em></p>
            </article>
        @endforeach
    </div>
</x-site-layout>
