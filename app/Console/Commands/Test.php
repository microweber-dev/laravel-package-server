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

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-command:run';

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

        $package = Package::where('id',262)->first();

        dispatch_sync(new ProcessPackageSatis($package->id, $package->name));

        return 0;
    }
}
