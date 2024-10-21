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
        <link rel="alternate" type="application/rss+xml" title="{{ $page->title }} - Main Feed" href="{{ $page->baseUrl }}/feeds/main.xml" />
    </head>
    <body>
        <div class="container masthead">
            <a href="/">Phil Stephens</a>
        </div>

        <div class="container">
            <div class="sidebar">
                <ul>
                    <li><a href="/about">About Me</a></li>
                    <li><a href="/now">Now</a></li>
                </ul>
            </div>
            <div class="content">
                @yield('body')
            </div>
        </div>
    </body>
</html>
