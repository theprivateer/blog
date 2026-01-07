<x-site-layout>
    <div class="space-y-8 relative">
        <h1 class="text-4xl font-bold">Moments</h1>

        @foreach($moments as $moment)
                <article class="p-6 border-1">
                    <div class="prose">
                        <p class="mt-2 text-slate-500">
                            {{ $moment->created_at->format('l, j F Y') }}
                        </p>

                        {!! $moment->render() !!}
                    </div>
                </article>
        @endforeach

        {!! $moments->links() !!}
    </div>
</x-site-layout>
