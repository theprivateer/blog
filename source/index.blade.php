@extends('_layouts.main')

@section('body')
<div>
    @foreach($posts->take(30)->groupBy(function ($item) { return date('l, j F Y', $item->date); }) as $key => $day)
        <h3 class="date">{{ $key }}</h3>
        @foreach($day as $post)
            <article class="post">
                <h2 @if($post->link) class="with-subheading" @endif>
                    <a href="{{ $post->link ?? $post->getUrl() }}"
                        class="{{ $post->link ? 'link' : ''}}"
                        >{{ $post->title }}</a>
                    @if($post->link)
                    <a href="{{ $post->getUrl() }}">★</a>
                    @endif
                </h2>

                @if($post->link)
                <p>{{ parse_url($post->link, PHP_URL_HOST) }}</p>
                @endif

                <div class="post-content">
                    {!! $post->getContent() !!}
                </div>
            </article>
        @endforeach
    @endforeach
</div>
@endsection
