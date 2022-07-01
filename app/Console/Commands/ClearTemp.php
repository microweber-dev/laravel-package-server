<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;

class ClearTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:clear-temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $getRunningPackages = Package::where('clone_status', Package::CLONE_STATUS_WAITING)
                            ->orWhere('clone_status', Package::CLONE_STATUS_QUEUED)
                            ->orWhere('clone_status', Package::CLONE_STATUS_RUNNING)
                            ->orWhere('clone_status', Package::CLONE_STATUS_CLONING)
                            ->get();

        if ($getRunningPackages->count() == 0) {

            // No running packages
            $folders = [];
            $folders[] = storage_path('package-manager-worker');
            $folders[] = storage_path('package-manager-worker-builds');
            $folders[] = storage_path('package-manager-worker-builds-temp');
            $folders[] = storage_path('repositories-satis');

            foreach ($folders as $folder) {
                rmdir_recursive($folder);
            }
        }

        return 0;
    }
}
