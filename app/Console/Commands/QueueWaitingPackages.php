<?php

namespace App\Console\Commands;

use App\Helpers\GithubHelper;
use App\Jobs\ProcessPackageSatis;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Console\Command;

class QueueWaitingPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:queue-waiting-packages';

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
        $this->info('Start dispatching jobs...');

        $getWaitingPackages = Package::where('clone_status', Package::CLONE_STATUS_WAITING)->get();
        if ($getWaitingPackages == null) {
            $this->error('No packages for dispatching. Time: ' . date('Y-m-d H:i:s'));
            return 0;
        }

        $packagesForDispatchingNum = (int) $getWaitingPackages->count();
        $this->info('Packages for dispatching:' . $packagesForDispatchingNum);

        $countDispatchedPackages = 0;
        foreach ($getWaitingPackages as $package) {

            dispatch(new ProcessPackageSatis($package->id, $package->name));

            $package->clone_status = Package::CLONE_STATUS_QUEUED;
            $package->clone_queue_at = Carbon::now();
            $package->save();

            $this->info('Dispatch:' . $package->name);

            sleep(10);
            $countDispatchedPackages++;
        }

        $this->info('Dispatched packages:' . $countDispatchedPackages);
        $this->info('Waiting Packages:' . ($packagesForDispatchingNum - $countDispatchedPackages));

        return 0;
    }
}
