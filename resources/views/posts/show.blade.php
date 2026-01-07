<x-site-layout>
    <article>
        {{-- @if($post->link)
        <h1 class="font-bold flex items-center gap-2">
            <a href="{{ $post->link }}" class="underline">{{ $post->title }}</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                <path d="M12.232 4.232a2.5 2.5 0 0 1 3.536 3.536l-1.225 1.224a.75.75 0 0 0 1.061 1.06l1.224-1.224a4 4 0 0 0-5.656-5.656l-3 3a4 4 0 0 0 .225 5.865.75.75 0 0 0 .977-1.138 2.5 2.5 0 0 1-.142-3.667l3-3Z" />
                <path d="M11.603 7.963a.75.75 0 0 0-.977 1.138 2.5 2.5 0 0 1 .142 3.667l-3 3a2.5 2.5 0 0 1-3.536-3.536l1.225-1.224a.75.75 0 0 0-1.061-1.06l-1.224 1.224a4 4 0 1 0 5.656 5.656l3-3a4 4 0 0 0-.225-5.865Z" />
            </svg>
        </h1>
        <p class="text-orange-500">{{ parse_url($post->link, PHP_URL_HOST) }}</p>
        @else
        <h1 class="font-bold text-orange-500">
            {{ $post->title }}
        </h1>
        @endif --}}

        {{-- <p class="mt-2 mb-6 text-slate-500">{{ $post->published_at->format('l, j F Y') }}</p> --}}

        @if($post->update)
        <div class="mb-6 prose max-w-none p-4 border-2 border-orange-500">
            <h4 class="text-base mb-3">Update</h4>
            {{ $post->update }}
        </div>
        @endif

        {{-- @if($toc)
            <div class="lg:grid lg:grid-cols-4 gap-6">
                <div class="lg:col-span-1 prose max-w-none prose-headings:text-base prose-headings:font-normal prose-a:no-underline order-last mb-6 p-4 border-2 border-slate-200 lg:border-0 lg:p-0">
                    <h3 class="text-slate-500 mb-6">Table of contents</h3>

                    {!! $toc !!}
                </div>

                <div class="lg:col-span-3">
        @endif --}}

        <div class="prose max-w-none _prose-headings:text-base">
            <h1 class="mb-2">
                {{ $post->title }}
            </h1>

            <p class="mt-2 mb-6 text-slate-500">{{ $post->published_at->format('l, j F Y') }}</p>

            {!! $post->render() !!}
        </div>

        {{-- @if($toc)
                </div>
            </div>
        @endif --}}

        <a href="mailto:hello@philstephens?subject=Comment: {{ $post->title }}" class="mt-8 inline-flex items-center gap-2 text-slate-500 hover:text-slate-900">
            Email a comment

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 -rotate-45">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
        </a>
    </article>

</x-site-layout>
