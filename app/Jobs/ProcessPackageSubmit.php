<?php

namespace App\Jobs;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symplify\GitWrapper\GitWrapper;

class ProcessPackageSubmit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $packageModel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->packageModel = $package;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $repositoriesPath = storage_path() . '/repositories/'.Str::slug($this->packageModel->repository_url);

        $gitWrapper = new GitWrapper();
        $gitWrapper->setPrivateKey(env('SSH_KEY_PATH'));

        $git = $gitWrapper->cloneRepository($this->packageModel->repository_url, $repositoriesPath,[
            'verbose'=>true,
            'depth'=>1
        ]);

        dump($git->status());

    }
}
