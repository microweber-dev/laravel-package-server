<?php

namespace App\Console\Commands;

use App\Helpers\Base;
use App\Helpers\PackageManagerGitWorker;
use App\Helpers\RepositoryMediaProcessHelper;
use App\Jobs\ProcessPackageSatis;
use App\Models\Package;
use CzProject\GitPhp\Git;
use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TestBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build repository package with saits';

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
        $this->info('Start job work...');

        $getPackage = Package::where('id', 2)->first();
        if ($getPackage == null) {
            $this->error('No packages for processing. Time: ' . date('Y-m-d H:i:s'));
            return 0;
        }

        $this->info('Package: ' . $getPackage->name);

//        $packageJson = json_decode($getPackage->package_json, true);
//        $latestVersion = \App\Helpers\SatisHelper::getLatestVersionFromPackage($packageJson);
//        dd($latestVersion);

        $getPackage->clone_status = Package::CLONE_STATUS_WAITING;
        $getPackage->save();

        $run = new ProcessPackageSatis($getPackage->id, $getPackage->name);
        $status = $run->handle();

        dd($status);

        $this->info('Job work done.');

        return 0;
    }
}
