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
                    --font-size-base: 120%;
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
		<main class="container">
            <section>
                {!! $page->render() !!}
            </section>
        </main>
	</body>
</html>
