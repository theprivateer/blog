<x-site-layout>
    <div class="space-y-8 relative">
        <div class="mt-6 prose max-w-none p-4 border-2 border-orange-500">
            <p>
                These are my <a href="https://slashpages.net/">Slash pages</a>.
                They are common pages that can be found on personal websites and help to describe me in a more structured way.
                I'm constantly adding and refining the content here so be sure to check back often.
            </p>
        </div>

        @foreach($slashes as $slash)
                <hr class="dot-fill" />

                <article>
                    <h2 class="font-bold text-orange-500">
                        <a href="{{ route('slashes.show', $slash->slug) }}" class="underline">/{{ $slash->slug }}</a>
                    </h2>

                    @if($slash->modified)
                    <p class="mt-2 text-slate-500">Updated {{ Carbon\Carbon::parse($slash->modified)->format('l, j F Y') }}</p>
                    @endif
                </article>
        @endforeach
    </div>
</x-site-layout>
