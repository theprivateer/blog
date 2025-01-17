<x-site-layout>
    <article>
        @if($parent)
        <p class="mb-6">
            <a href="{{ route('slashes.show', $parent->slug) }}" class="text-slate-500 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                  </svg>

                {{ $parent->title }}
            </a>
        </p>
        @endif

        <h1 class="font-bold -ms-4 ps-4 border-s-2 border-purple-500 mb-2">
            {{ $slash->title }}
        </h1>

        @if($slash->modified)
            <p class="mb-6 text-slate-500">Updated {{ Carbon\Carbon::parse($slash->modified)->format('l, j F Y') }}</p>
        @endif

        @if($slash->callout)
        <div class="mb-6 prose max-w-none p-4 border-2 border-purple-500">
            {{ $slash->callout }}
        </div>
        @endif
        @if($toc)
            <div class="lg:grid lg:grid-cols-4 gap-6">
                <div class="lg:col-span-1 prose max-w-none prose-headings:text-base prose-headings:font-normal prose-a:no-underline order-last mb-6 p-4 border-2 border-slate-200 lg:border-0 lg:p-0">
                    <h3 class="text-slate-500 mb-6">Table of contents</h3>

                    {!! $toc !!}
                </div>

                <div class="lg:col-span-3">
        @endif

        <div class="prose max-w-none prose-headings:text-base">
            {{ $slash->contents }}
        </div>

        @if($toc)
                </div>
            </div>
        @endif
    </article>
</x-site-layout>
