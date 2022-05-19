<?php

namespace App\Console\Commands;

use App\Helpers\Base;
use App\Helpers\RepositoryMediaProcessHelper;
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


        $gitRunnerRepositoryUrl = 'https://build:glpat-s947uAnt_G-E7Sezwozb@gitlab.com/mw-internal/package-manager/package-manager-worker.git';

        $allWorkersPath = storage_path() . '/package-manager-worker';
        if (!is_dir($allWorkersPath)) {
            mkdir_recursive($allWorkersPath);
        }

        $workerGitPath = $allWorkersPath . '/gitlab-worker';
        rmdir_recursive($workerGitPath,false);

        $repositoryClone = shell_exec('cd '.$allWorkersPath.' && git clone ' . $gitRunnerRepositoryUrl . ' ' . $workerGitPath);

        shell_exec('cd '.$workerGitPath.' && git config user.email "bot@microweber.com" &&  git config user.name "mw-bot"');


        $git = new Git();
        $repository = $git->open($workerGitPath);

        file_put_contents($workerGitPath . '/time.txt', time());

        if ($repository->hasChanges()) {
            $repository->addAllChanges();
            $repository->commit('update');
            $repository->push();
        }

        return 0;
    }
}
