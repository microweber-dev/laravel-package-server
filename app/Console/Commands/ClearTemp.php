<?php

namespace App\Console\Commands;

use App\Helpers\GithubHelper;
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
        $busyWorkers = GithubHelper::getBusyWorkers();
        if ($busyWorkers != 0) {
            // Wait for all workers to finish
           return;
        }

        $getRunningPackages = Package::where('clone_status', Package::CLONE_STATUS_WAITING)
                            ->orWhere('clone_status', Package::CLONE_STATUS_QUEUED)
                            ->orWhere('clone_status', Package::CLONE_STATUS_RUNNING)
                            ->orWhere('clone_status', Package::CLONE_STATUS_CLONING)
                            ->get();

        if ($getRunningPackages->count() == 0) {

            // No running packages
            $folders = [];
            $folders[] = ['path'=>storage_path('package-manager-worker'), 'allowed_files_for_delete'=>['zip']];
            $folders[] = ['path'=>storage_path('package-manager-worker-builds'), 'allowed_files_for_delete'=>['zip']];
            $folders[] = ['path'=>storage_path('package-manager-worker-builds-temp'), 'allowed_files_for_delete'=>['zip']];
            $folders[] = ['path'=>storage_path('repositories-satis'), 'allowed_files_for_delete'=>['zip']];

            foreach ($folders as $folder) {

                $scanDir = scandir($folder['path']);
                if (!empty($scanDir)) {
                    foreach ($scanDir as $pathOrFile) {
                        if ($pathOrFile == '.' || $pathOrFile == '..') continue;

                        $pathOrFileFullPath = $folder['path'] . DIRECTORY_SEPARATOR . $pathOrFile;
                        if (is_dir($pathOrFileFullPath)) {
                            if (in_array('folders', $folder['allowed_files_for_delete'])) {
                                $this->info('Delete folder: ' . $pathOrFileFullPath);
                                rmdir_recursive($pathOrFileFullPath, false);
                            }
                        }

                        if (is_file($pathOrFileFullPath)) {
                            $getFileExt = pathinfo($pathOrFileFullPath);
                            if (isset($getFileExt['extension'])) {
                                if (in_array($getFileExt['extension'], $folder['allowed_files_for_delete'])) {
                                    $this->info('Delete file: ' . $pathOrFileFullPath);
                                    unlink($pathOrFileFullPath);
                                }
                            }
                        }
                    }
                }
            }
        }

        return 0;
    }
}
