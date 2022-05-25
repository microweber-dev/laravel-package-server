<?php

namespace App\Console\Commands;

use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckBuildedPackagesStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:check-statuses';

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
        $getPackages = Package::select(['id','name','description','clone_status'])
            ->whereNotIn('clone_status', [Package::CLONE_STATUS_SUCCESS])
            ->whereDate('created_at', '<=', Carbon::now()->subMinutes(30))
            ->get();

        foreach ($getPackages as $package) {
            $package->clone_status = Package::CLONE_STATUS_FAILED;
            $package->save();
        }

        return 0;
    }
}
