@extends('_layouts.main')

@section('body')
    <article>
        <h1>{{ $page->title }}</h1>

        @if($page->modified)
        <p><em>Updated {{ date('l, j F Y', $page->modified) }}</em></p>
        @endif

        @yield('content')
    </article>
@endsection
