<x-site-layout>
    <section class="prose max-w-none">
		{!! $page->render() !!}
    </section>
    <div class="space-y-8 relative mt-16">
        <h2 class="text-2xl font-bold">Recent Posts</h2>

        @foreach($posts as $post)
            {{-- <hr class="dot-fill" /> --}}

            <article>
                <h2 class="font-bold">
                    <a href="{{ route('posts.show', $post->slug) }}" class="underline">{{ $post->title }}</a>
                </h2>
                <p class="mt-2 text-slate-500">{{ $post->published_at->format('l, j F Y') }}</p>
            </article>
        @endforeach
    </div>
</x-site-layout>
