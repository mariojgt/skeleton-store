<?php

namespace Skeleton\Store\Commands;

use File;
use Illuminate\Console\Command;

class Republish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'republish:skeleton-store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will copy the resource files from back to the package';

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
        $bar = $this->output->createProgressBar(2);
        $bar->start();

        $this->moveFileOrFolder(
            resource_path('vendor/SkeletonAdmin/js/backend/Pages/BackEnd/Vendor/skeleton-store'),
            __DIR__ . '/../../Publish/Resource/pageBackend',
            $bar
        );

        $this->moveFileOrFolder(
            resource_path('vendor/SkeletonAdmin/js/frontend/Pages/FrontEnd/Vendor/skeleton-store'),
            __DIR__ . '/../../Publish/Resource/pageFrontend',
            $bar
        );

        $bar->finish(); // Finish the progress bar
        $this->newLine();
        $this->info('The command was successful!');
    }

    /**
     * @param mixed $target // The folder we want to copy
     * @param mixed $destination // The folder we want to copy to
     * @param mixed $bar // The progress bar or the command
     *
     * @return void
     */
    private function moveFileOrFolder($target, $destination, $bar, $isFile = false): void
    {
        if ($isFile) {
            File::copy($target, $destination);
        } else {
            File::copyDirectory($target, $destination);
        }
        $bar->advance(); // Little Progress bar
    }
}
