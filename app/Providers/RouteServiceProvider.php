<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseRouteServiceProvider;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();

        // Post filenames include a timestamp so we can't just
        // use the Sheets get() method to locate the file
        Route::bind('post', function ($slug) {
            return $this->app->make(\Spatie\Sheets\Sheets::class)
                ->collection('posts')
                ->all()
                ->where('slug', $slug)
                ->first() ?? abort(404);
        });

        Route::bind('slash', function ($slug) {
            return $this->app->make(\Spatie\Sheets\Sheets::class)
                ->collection('slashes')
                ->get($slug) ?? abort(404);
        });
    }
}
