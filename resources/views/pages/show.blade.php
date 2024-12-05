<x-site-layout>
    <article>
        <h2 class="font-bold -ms-4 ps-4 border-s-2 border-purple-500">
            {{ $page->title }}
        </h2>

        @if($page->modified)
            <p class="mt-4 text-slate-500">Updated {{ Carbon\Carbon::parse($page->modified)->format('l, j F Y') }}</p>
        @endif

        <div class="mt-6 prose max-w-none prose-headings:text-base">
            {{ $page->contents }}
        </div>
    </article>
</x-site-layout>
