<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Behind a TLS-terminating proxy/CDN (Cloudflare, a load balancer, LiteSpeed
        // in proxy mode) the app receives plain HTTP, so without this every generated
        // URL — assets, redirects, Ziggy routes — comes out http:// and trips the
        // browser's mixed-content blocking on an https page. When the operator declares
        // an https APP_URL, force the scheme so all generated URLs match the real one.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
