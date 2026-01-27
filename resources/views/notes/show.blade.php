<x-site-layout>
    <div class="space-y-8 relative">
        <h1 class="text-4xl font-bold">Notes</h1>

        <article>
            @if($note->link)
            <h2 class="font-bold">
                <a href="{{ $note->link }}" class="underline">{{ $note->title }}</a>
            </h2>
            @else
            <h2 class="font-bold">
                {{ $note->title }}
            </h2>
            @endif

            <div class="prose">
                <p class="mt-2 text-slate-500">
                    {{ $note->created_at->format('l, j F Y') }}
                    @if($note->link)
                    | {{ parse_url($note->link, PHP_URL_HOST) }}
                    @endif
                </p>

                {!! $note->render() !!}
            </div>
        </article>
    </div>
</x-site-layout>
