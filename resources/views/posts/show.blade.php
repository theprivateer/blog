<x-site-layout>
    <article>
        @if($post->link)
        <h2 class="font-bold -ms-4 ps-4 border-s-2 border-purple-500 flex items-center gap-2">
            <a href="{{ $post->link }}" class="underline">{{ $post->title }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
            </svg>
        </h2>
        <p class="text-slate-400">{{ parse_url($post->link, PHP_URL_HOST) }}</p>
        @else
        <h2 class="font-bold -ms-4 ps-4 border-s-2 border-purple-500">
            {{ $post->title }}
        </h2>
        @endif

        <p class="mt-2 text-slate-500">{{ $post->date->format('l, j F Y') }}</p>

        @if($post->update)
        <div class="mt-6 prose max-w-none p-4 border-2 border-purple-500">
            <h4 class="text-base mb-3">Update</h4>
            {{ $post->update }}
        </div>
        @endif

        <div class="mt-6 prose max-w-none prose-headings:text-base">
            {{ $post->contents }}
        </div>
    </article>

</x-site-layout>
