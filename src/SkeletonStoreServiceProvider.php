<?php

namespace Skeleton\Store;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Skeleton\Store\Models\StoreSetting;
use Skeleton\Store\Events\UserSubscribedToPlan;
use Skeleton\Store\Events\UserUnsubscribedToPlan;
use Skeleton\Store\Listeners\SubscribeUserToPlan;
use Skeleton\Store\Listeners\UnsubscribeUserToPlan;

class SkeletonStoreServiceProvider extends ServiceProvider
{
    protected $listen = [
        UserSubscribedToPlan::class => [
            SubscribeUserToPlan::class,
        ],
        UserUnsubscribedToPlan::class => [
            UnsubscribeUserToPlan::class,
        ],
    ];

    protected $routeMiddleware = [
        'subscription' => \Skeleton\Store\Middleware\PaymentHandlerMiddleware::class,
        'capability' => \Skeleton\Store\Middleware\CheckCapability::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the event listeners
        $this->registerEvents();

        // Load the commands
        $this->loadCommands();

        // Load backend routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/Backend/web/web.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/Backend/api/api.php');
        // Load frontend routes
        $this->loadRoutesFrom(__DIR__ . '/Routes/Frontend/web/web.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/Frontend/api/api.php');

        // Load Migrations
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        Cache::remember('ecommerceStore', 60 * 60 * 24, function () {
            $configArray = [];

            // check if the table exists before querying it
            if (\Schema::hasTable('store_settings')) {
                // Load the store config
                $configArray = StoreSetting::all()->pluck('value', 'key')->toArray();
            }
            // add the config to the config helper
            config(['ecommerceStore' => $configArray]);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRouteMiddleware();
        $this->publish();
    }

    protected function registerRouteMiddleware()
    {
        foreach ($this->routeMiddleware as $key => $middleware) {
            $this->app['router']->aliasMiddleware($key, $middleware);
        }
    }

    public function publish()
    {
        $this->publishes([
            __DIR__ . '/../Publish/Resource/pageBackend' => resource_path('vendor/SkeletonAdmin/js/backend/Pages/BackEnd/Vendor/skeleton-store'),
            __DIR__ . '/../Publish/Resource/pageFrontend' => resource_path('vendor/SkeletonAdmin/js/frontend/Pages/BackEnd/Vendor/skeleton-store'),
            __DIR__ . '/../Config' => config_path(''),
            __DIR__ . '/../Helper' => app_path('Helpers'),
        ]);
    }

    /**
     * Inject the commands
     * @return void
     */
    public function loadCommands()
    {
        // Autoload all the commands from the folder Commands
        if ($this->app->runningInConsole()) {
            $availableCommandsPath =  __DIR__ . '/Commands';
            // Now get all the commands classes
            $commandClasses = array_map(function ($path) {
                return 'Skeleton\Store\Commands\\' . basename($path, '.php');
            }, glob($availableCommandsPath . '/*.php'));
            $this->commands($commandClasses);
        }
    }

     /**
     * Register events and listeners
     *
     * @return void
     */
    protected function registerEvents()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                \Illuminate\Support\Facades\Event::listen($event, $listener);
            }
        }
    }
}
