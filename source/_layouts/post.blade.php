@extends('_layouts.main')

@section('body')
    <article>
        @if($page->link)
        <h1><a href="{{ $page->link }}">{{ $page->title }}</a></h1>
        <p>{{ parse_url($page->link, PHP_URL_HOST) }}</p>
        @else
        <h1>{{ $page->title }}</h1>
        @endif

        <p>{{ date('l, j F Y', $page->date) }}</p>

        @yield('content')
    </article>
@endsection
