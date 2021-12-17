<?php

namespace App\Jobs;

use App\Models\Package;
use App\RepositoryPathHelper;
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

        $packageModel->clone_status = Package::CLONE_STATUS_RUNNING;
        $packageModel->save();

        try {

            $repositoryPath = RepositoryPathHelper::getRepositoriesClonePath($packageModel->id);

            if (is_dir($repositoryPath)) {
                File::deleteDirectory($repositoryPath);
            }

            $gitWrapper = new GitWrapper();
         //   $gitWrapper->setPrivateKey(env('SSH_KEY_PATH'));


            $git = $gitWrapper->cloneRepository($packageModel->repository_url, $repositoryPath, [
                'verbose' => true,
             //   'depth' => 1
            ]);
            $status = $git->status();

            $composerJsonFile = $repositoryPath . 'composer.json';
            if (!is_file($composerJsonFile)) {
                throw new \Exception('composer.json missing');
            }

            $openComposerJson = json_decode(file_get_contents($composerJsonFile));

            $packageModel->clone_status = Package::CLONE_STATUS_SUCCESS;
            $packageModel->clone_log = $status;
            $packageModel->name = $openComposerJson->name;
            $packageModel->is_cloned = 1;
            $packageModel->save();

            dispatch(new ProcessPackageSatis($packageModel->id));

        } catch (\Exception $e) {

            $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
            $packageModel->clone_log = $e->getMessage();
            $packageModel->save();

        }
    }

}
