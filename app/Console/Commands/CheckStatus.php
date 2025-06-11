<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use App\Jobs\ProcessPackageSatis;

class CheckStatus extends Command
{
    protected $signature = 'check-status {--id=}';
    protected $description = 'Check and process package status by ID';

    public function handle()
    {
        $id = $this->option('id');
        if (!$id) {
            $this->error('No package id provided.');
            return 1;
        }

        $package = Package::find($id);
        if (!$package) {
            $this->error("Package with id {$id} not found.");
            return 1;
        }






        if ($package->clone_status == Package::CLONE_STATUS_SUCCESS) {
            $this->info("Package '{$package->name}' [ID: {$id}] is ready (status: SUCCESS).");
            return 0;
        }

        $this->info("Processing package '{$package->name}' [ID: {$id}] (current status: {$package->clone_status})...");
        $job = new ProcessPackageSatis($package->id, $package->name);
        $job->handle();

        // Reload package status after processing
        $package->refresh();

        if ($package->clone_status == Package::CLONE_STATUS_SUCCESS) {
            $this->info("Package '{$package->name}' [ID: {$id}] is now ready (status: SUCCESS).");
            return 0;
        } else {
            $this->error("Package '{$package->name}' [ID: {$id}] failed to process. Status: {$package->clone_status}");
            return 1;
        }
    }
}
