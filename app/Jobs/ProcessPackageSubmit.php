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

    public $packageId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($packageId)
    {
        $this->packageId = $packageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $packageModel = Package::where('id', $this->packageId)->first();

        $repositoryPath = storage_path() . '/repositories/' . $packageModel->id;

        if (is_dir($repositoryPath)) {
            File::deleteDirectory($repositoryPath);
        }

        $gitWrapper = new GitWrapper();
     //   $gitWrapper->setPrivateKey(env('SSH_KEY_PATH'));

        try {
            $git = $gitWrapper->cloneRepository($packageModel->repository_url, $repositoryPath, [
                'verbose' => true,
                'depth' => 1
            ]);
            $status = $git->status();

            $packageModel->clone_status = 'success';
            $packageModel->clone_log = $status;
            $packageModel->is_cloned = 1;
            $packageModel->save();

        } catch (\Exception $e) {

            $packageModel->clone_status = 'failed';
            $packageModel->clone_log = $e->getMessage();
            $packageModel->save();

        }
    }
}
