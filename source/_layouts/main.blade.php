<!DOCTYPE html>
<html lang="{{ $page->language ?? 'en' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="canonical" href="{{ $page->getUrl() }}">
        <meta name="description" content="{{ $page->description }}">
        <link rel="shortcut icon" href="{{ $page->baseUrl }}/assets/images/favicon.png">

        <title>{{ $page->title }}</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body>
        <div class="container masthead">
            <a href="/">Phil Stephens</a>
        </div>

        <div class="container">
            {{-- <div class="sidebar">Sidebar</div> --}}
            <div class="content">
                @yield('body')
            </div>
        </div>
    </body>
</html>
