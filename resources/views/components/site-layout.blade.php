<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <!--
    Greetings fellow 'view-sourcer'!
    Welcome to my personal corner of the web.
    -->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ url('img/favicon.png') }}">

        <title>{{ config('app.name') }}</title>

        <meta name="description" content="The personal blog of Phil Stephens." />

        @vite(['resources/css/app.css'])

        <!-- Long live RSS! -->
        <x-feed-links />

        {{-- <link rel="micropub" href="{{ route('micropub') }}"> --}}

    </head>
    <body class="font-mono antialiased">
        <div class="max-w-2xl mx-auto py-16 px-6">
            <div class="flex items-center justify-between">
                <div class="flex gap-6 items-center">
                    <a href="{{ route('home') }}" class="_-ms-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="23" height="32" fill="currentColor" viewBox="0 0 233 320" >
                            <g transform="matrix(1,0,0,1,-1.0608,-1.85537)">
                                <g transform="matrix(4.16667,0,0,4.16667,-148.94,-3122.08)">
                                    <path d="M88.606,783.783C89.039,781.334 91.318,765.257 77.789,759.682C77.789,759.681 77.79,759.68 77.79,759.679C77.973,757.36 77.169,756.309 77.169,756.309C76.293,758.498 73.915,758.484 73.915,758.484C74.599,753.22 71.917,749.745 71.917,749.745C70.167,757.622 60.102,757.622 60.134,757.78L60.132,757.794C35.567,758.343 38.737,780.848 39.256,783.783C38.367,783.529 35.667,783.197 36.034,788.692C36.463,795.132 37.316,802.829 41.479,802.706L41.504,802.915C42.598,825.451 63.384,826.545 63.384,826.545L64.697,826.545L64.697,826.529C66.911,826.336 85.33,824.095 86.358,802.915L86.383,802.706C90.546,802.829 91.399,795.132 91.829,788.692C92.195,783.197 89.495,783.529 88.606,783.783M64.649,788.056L60.702,779.088L48.505,784.11L63.214,767.25L67.16,775.859L79.357,771.196L64.649,788.056Z" style="fill-rule:nonzero;"/>
                                </g>
                            </g>
                        </svg>
                        <span class="sr-only">Phil Stephens</span>
                    </a>

                    <ul class="hidden sm:flex gap-2 ">
                        <li><a href="{{ route('posts.index') }}">Posts</a></li>
                        <li class="text-slate-300">|</li>
                        <li><a href="/notes">Notes</a></li>
                        <li class="text-slate-300">|</li>
                        <li><a href="/moments">Moments</a></li>
                        <li class="text-slate-300">|</li>
                        <li><a href="/about">About</a></li>
                        <li class="text-slate-300">|</li>
                        <li><a href="/now">Now</a></li>
                        <li class="text-slate-300">|</li>
                        <li><a href="/work">Work</a></li>
                    </ul>

                </div>
            </div>

            <main class="mt-16">
                {{ $slot }}
            </main>

            <hr class="dot-fill my-8" />

            <footer class="flex justify-between">
                <p>&copy; Phil Stephens 2017-{{ date('Y') }}</p>
                <ul class="flex gap-2">
                    {{-- <li><a href="https://themarkup.org/blacklight?location=eu&device=desktop&force=false&url=philstephens.com">No tracking</a></li>
                    <li class="text-slate-300">|</li>
                    <li><a href="https://www.websitecarbon.com/website/philstephens-com/">0.01g of CO<sub>2</sub></a></li>
                    <li class="text-slate-300">|</li> --}}
                    <li><a href="/slashes">/slashes</a></li>
                </ul>
            </footer>
        </div>
    </body>
</html>
