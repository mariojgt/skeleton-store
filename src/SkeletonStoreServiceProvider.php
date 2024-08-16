<?php

namespace Skeleton\Store;

use Illuminate\Support\ServiceProvider;

class SkeletonStoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
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
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publish();
    }

    public function publish()
    {
        $this->publishes([
            __DIR__ . '/../Publish/Resource/pageBackend' => resource_path('vendor/SkeletonAdmin/js/backend/Pages/Vendor/skeleton-store'),
            __DIR__ . '/../Publish/Resource/pageFronted' => resource_path('vendor/SkeletonAdmin/js/frontend/Pages/Vendor/skeleton-store'),
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
}
