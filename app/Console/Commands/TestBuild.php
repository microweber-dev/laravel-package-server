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

        $getWaitingPackage = Package::where('id', 161)->first();
        if ($getWaitingPackage == null) {
            $this->error('No packages for processing. Time: ' . date('Y-m-d H:i:s'));
            return 0;
        }

        $this->info('Package: ' . $getWaitingPackage->name);

        $run = new ProcessPackageSatis($getWaitingPackage->id, $getWaitingPackage->name);
        $run->handle();

        $this->info('Job work done.');

        return 0;
    }
}
