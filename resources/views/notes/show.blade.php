<x-site-layout :metadata="$metadata">
    <h1>Notes</h1>

    <article class="margin-end-4xl">
        <h3 class="margin-end-0">
            @if($note->link)
            <a href="{{ $note->link }}">{{  $note->title }}</a>
            @else
            {{  $note->title }}
            @endif
        </h3>
        <p class="text-muted">
            {{ $note->created_at->format('l, j F Y') }}
            @if($note->link)
            | {{ parse_url($note->link, PHP_URL_HOST) }}
            @endif
        </p>

        {!! $note->render() !!}
    </article>
</x-site-layout>
