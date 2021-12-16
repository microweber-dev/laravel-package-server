<?php

namespace App\Jobs;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
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
        $repositoryId = $this->packageModel->id;
        $repositoryUrl = $this->packageModel->repository_url;
        $repositoryPath = storage_path() . '/repositories/' . $repositoryId;

        if (is_dir($repositoryPath)) {
            File::deleteDirectory($repositoryPath);
        }

        $gitWrapper = new GitWrapper();
     //   $gitWrapper->setPrivateKey(env('SSH_KEY_PATH'));

        try {
            $git = $gitWrapper->cloneRepository($repositoryUrl, $repositoryPath, [
                'verbose' => true,
                'depth' => 1
            ]);
            $status = $git->status();

            $this->packageModel->clone_status = 'success';
            $this->packageModel->clone_log = $status;
            $this->packageModel->is_cloned = 1;
            $this->packageModel->save();

        } catch (\Exception $e) {

            $this->packageModel->clone_status = 'failed';
            $this->packageModel->clone_log = $e->getMessage();
            $this->packageModel->save();

        }
    }
}
