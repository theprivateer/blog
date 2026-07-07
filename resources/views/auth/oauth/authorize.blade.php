<!DOCTYPE html>
<html lang="en-us">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="{{ url('img/favicon.png') }}">

        <title>Authorize {{ $client->name }} | Phil Stephens</title>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/kelpui@1/css/kelp.css">

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inclusive+Sans:ital,wght@0,300..700;1,300..700&display=swap');

            @layer kelp.theme {
                :root {
                    --font-size-base: 150%;
                    --font-primary: "Inclusive Sans", sans-serif;
                    --font-secondary: "Inclusive Sans", sans-serif;
                }
            }

            .authorize-card {
                max-width: 32rem;
                margin: 4rem auto;
            }

            .box {
                border: solid 2px var(--color-border-accent);
                border-radius: var(--border-radius-m);
            }
        </style>
    </head>
    <body>
        <main class="container">
            <section class="authorize-card box padding-3xl">
                <h1>Authorize {{ $client->name }}</h1>

                <p>
                    Signed in as <strong>{{ $user->name }}</strong>. This application is requesting access to your
                    account at <strong>{{ config('app.name') }}</strong> with the following abilities:
                </p>

                @if (count($scopes) > 0)
                    <ul>
                        @foreach ($scopes as $scope)
                            <li>{{ $scope->description }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="split margin-start-xl">
                    <form method="POST" action="{{ route('passport.authorizations.approve') }}">
                        @csrf
                        <input type="hidden" name="state" value="{{ $request->query('state') }}">
                        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">

                        <button type="submit" class="button primary">Authorize</button>
                    </form>

                    <form method="POST" action="{{ route('passport.authorizations.deny') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="state" value="{{ $request->query('state') }}">
                        <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                        <input type="hidden" name="auth_token" value="{{ $authToken }}">

                        <button type="submit" class="button">Cancel</button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
