<!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ url('img/favicon.png') }}">

		<title>Phil Stephens | {{ $metadata->title ?? $metadata->parent->title ?? '' }}</title>
		<meta name="description" content="{{ $metadata->description ?? config('metadata.default') }}">

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/kelpui@1/css/kelp.css">
        {{-- <script type="module" src="https://cdn.jsdelivr.net/npm/kelpui@1/js/kelp.js"></script> --}}

        <x-feed-links />

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inclusive+Sans:ital,wght@0,300..700;1,300..700&display=swap');

            @layer kelp.theme {
                :root {
                    --font-size-base: 150%;
                    --font-primary: "Inclusive Sans", sans-serif;
		            --font-secondary: "Inclusive Sans", sans-serif;
                }
            }

            h1 {
                line-height: 1.2;
            }

            .logo {
                display: flex;
                align-items: center;
                gap: 0.25rem;
            }

            .logo svg {
                width: 1.5rem;
                height: auto;
            }

            .box {
                border: solid 2px var(--color-border-accent);
                border-radius: var(--border-radius-m);
            }

            article[role="blog"] img {
                max-width: 80%;
                display: block;
                margin: 0 auto;
            }
        </style>
	</head>
	<body>
        <header class="container">
            <nav class="navbar">
                <a class="logo" href="/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="23" height="32" fill="currentColor" viewBox="0 0 233 320" >
                        <g transform="matrix(1,0,0,1,-1.0608,-1.85537)">
                            <g transform="matrix(4.16667,0,0,4.16667,-148.94,-3122.08)">
                                <path d="M88.606,783.783C89.039,781.334 91.318,765.257 77.789,759.682C77.789,759.681 77.79,759.68 77.79,759.679C77.973,757.36 77.169,756.309 77.169,756.309C76.293,758.498 73.915,758.484 73.915,758.484C74.599,753.22 71.917,749.745 71.917,749.745C70.167,757.622 60.102,757.622 60.134,757.78L60.132,757.794C35.567,758.343 38.737,780.848 39.256,783.783C38.367,783.529 35.667,783.197 36.034,788.692C36.463,795.132 37.316,802.829 41.479,802.706L41.504,802.915C42.598,825.451 63.384,826.545 63.384,826.545L64.697,826.545L64.697,826.529C66.911,826.336 85.33,824.095 86.358,802.915L86.383,802.706C90.546,802.829 91.399,795.132 91.829,788.692C92.195,783.197 89.495,783.529 88.606,783.783M64.649,788.056L60.702,779.088L48.505,784.11L63.214,767.25L67.16,775.859L79.357,771.196L64.649,788.056Z" style="fill-rule:nonzero;"/>
                            </g>
                        </g>
                    </svg>

                    {{-- Phil Stephens --}}
                </a>
                <ul>
                    <li><a href="/posts">Blog</a></li>
                    <li><a href="/notes">Notes</a></li>
                    {{-- <li><a href="/moments">Moments</a></li> --}}
                    <li><a href="/about">About</a></li>
                </ul>
            </nav>
        </header>
		<main class="container">
            {{ $slot }}
        </main>

        <footer class="container margin-start-6xl margin-end-4xl">
            <div class="split margin-end-4xl">
                <ul class="list-unstyled">
					<li>
                        <a href="https://linkedin.com/in/phil-stephens">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.994 24v-.001H24v-8.802c0-4.306-.927-7.623-5.961-7.623-2.42 0-4.044 1.328-4.707 2.587h-.07V7.976H8.489v16.023h4.97v-7.934c0-2.089.396-4.109 2.983-4.109 2.549 0 2.587 2.384 2.587 4.243V24zM.396 7.977h4.976V24H.396zM2.882 0C1.291 0 0 1.291 0 2.882s1.291 2.909 2.882 2.909 2.882-1.318 2.882-2.909A2.884 2.884 0 0 0 2.882 0z"/>
                            </svg>
                            View my LinkedIn profile
                        </a>
                    </li>
					<li>
                        <a href="https://github.com/theprivateer">
                            <svg fill="currentColor" width="30" height="30" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="m12 .5c-6.63 0-12 5.28-12 11.792 0 5.211 3.438 9.63 8.205 11.188.6.111.82-.254.82-.567 0-.28-.01-1.022-.015-2.005-3.338.711-4.042-1.582-4.042-1.582-.546-1.361-1.335-1.725-1.335-1.725-1.087-.731.084-.716.084-.716 1.205.082 1.838 1.215 1.838 1.215 1.07 1.803 2.809 1.282 3.495.981.108-.763.417-1.282.76-1.577-2.665-.295-5.466-1.309-5.466-5.827 0-1.287.465-2.339 1.235-3.164-.135-.298-.54-1.497.105-3.121 0 0 1.005-.316 3.3 1.209.96-.262 1.98-.392 3-.398 1.02.006 2.04.136 3 .398 2.28-1.525 3.285-1.209 3.285-1.209.645 1.624.24 2.823.12 3.121.765.825 1.23 1.877 1.23 3.164 0 4.53-2.805 5.527-5.475 5.817.42.354.81 1.077.81 2.182 0 1.578-.015 2.846-.015 3.229 0 .309.21.678.825.56 4.801-1.548 8.236-5.97 8.236-11.173 0-6.512-5.373-11.792-12-11.792z" />
                            </svg>
                            Find me on GitHub
                        </a>
                    </li>

                    <li>
                        <a href="https://www.strava.com/athletes/389199">
                            <svg fill="currentColor" width="30" height="30" viewBox="0 0 24 24" role="img" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/>
                            </svg>
                            Follow me on Strava
                        </a>
                    </li>
				</ul>

                <div class="split">
                    <ul class="list-unstyled">
                        <li><a href="/posts">Blog</a></li>
                        <li><a href="/notes">Notes</a></li>
                        <li><a href="/moments">Moments</a></li>
                        <li><a href="/follow">Follow</a></li>
                    </ul>

                    <ul class="list-unstyled">
                        <li><a href="/about">About</a></li>
                        <li><a href="/now">Now</a></li>
                        <li><a href="/uses">Uses</a></li>
                        <li><a href="/work">Work</a></li>
                    </ul>
                </div>
            </div>

            <div class="split">
                <p>&copy; Phil Stephens 2017-{{ date('Y') }}</p>
                <p><a href="/colophon">Colophon</a></p>
            </div>
        </footer>
	</body>
</html>
