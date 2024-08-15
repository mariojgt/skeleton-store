<?php

namespace Skeleton\Store\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Skeleton\Store\Database\Seeders\StoreSeeder;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:skeleton-store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will install skeleton-store package';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Call migrations
        Artisan::call('migrate');

        // Run the navigation seeder
        Artisan::call('db:seed', [
            '--class' => StoreSeeder::class,
        ]);

        // Publish the media library package
        Artisan::call('vendor:publish', [
            '--provider' => 'Skeleton\Store\SkeletonStoreServiceProvider',
            '--force'    => true,
        ]);
    }
}
