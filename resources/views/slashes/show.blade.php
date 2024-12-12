<x-site-layout>
    <article>
        <h2 class="font-bold -ms-4 ps-4 border-s-2 border-purple-500">
            {{ $slash->title }}
        </h2>

        @if($slash->modified)
            <p class="mt-4 text-slate-500">Updated {{ Carbon\Carbon::parse($slash->modified)->format('l, j F Y') }}</p>
        @endif

        @if($slash->callout)
        <div class="mt-6 prose max-w-none p-4 border-2 border-purple-500">
            {{ $slash->callout }}
        </div>
        @endif

        <div class="mt-6 prose max-w-none prose-headings:text-base">
            {{ $slash->contents }}
        </div>
    </article>
</x-site-layout>
