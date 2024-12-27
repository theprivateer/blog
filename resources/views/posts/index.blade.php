<x-site-layout>
    <div class="space-y-8 relative">
        @foreach($posts as $post)
                <hr class="dot-fill" />

                <article>
                    @if($post->link)
                    <h2 class="font-bold flex items-center gap-2">
                        <a href="{{ route('links.show', $post->slug) }}" class="underline">{{ $post->title }}</a>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                            <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
                        </svg>
                    </h2>

                    <p class="text-slate-400">{{ parse_url($post->link, PHP_URL_HOST) }}</p>
                    @else
                    <h2 class="font-bold -ms-4 ps-4 border-s-2 border-purple-500">
                        <a href="{{ route('posts.show', $post->slug) }}" class="underline">{{ $post->title }}</a>
                    </h2>
                    @endif

                    <p class="mt-2 text-slate-500">{{ $post->date->format('l, j F Y') }}</p>
                </article>
        @endforeach
    </div>
</x-site-layout>
