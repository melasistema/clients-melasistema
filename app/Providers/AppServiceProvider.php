<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        // Stable string aliases for the polymorphic `attachments.attachable_type`
        // column, so the DB never stores fully-qualified class names — attachments
        // survive a future model move/rename, and the values stay readable.
        Relation::morphMap([
            'client' => Client::class,
            'project' => Project::class,
            'task' => Task::class,
        ]);

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
