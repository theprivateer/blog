@if (app()->isProduction() && config('services.fathom.site_id'))
    <!-- Fathom - beautiful, simple website analytics -->
    <script src="https://cdn.usefathom.com/script.js" data-site="{{ config('services.fathom.site_id') }}" defer></script>
    <!-- / Fathom -->
@endif
