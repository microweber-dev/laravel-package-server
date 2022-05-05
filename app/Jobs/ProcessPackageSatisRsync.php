<?php

namespace App\Jobs;

use App\Helpers\RepositoryMediaProcessHelper;
use App\Models\Credential;
use App\Models\Package;
use App\Helpers\RepositoryPathHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symplify\GitWrapper\GitWrapper;

class ProcessPackageSatisRsync implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $packageId;
    public $satisRepositoryOutputPath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->packageId = $params['packageId'];
        $this->satisRepositoryOutputPath = $params['satisRepositoryOutputPath'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $packageModel = Package::where('id', $this->packageId)->first();

        $outputPublicDist = public_path() . '/dist/';
        if (!is_dir($outputPublicDist)) {
            mkdir($outputPublicDist, 0755, true);
        }

        $outputPublicMeta = public_path() . '/meta/';
        if (!is_dir($outputPublicMeta)) {
            mkdir($outputPublicMeta, 0755, true);
        }

        shell_exec("rsync -a $this->satisRepositoryOutputPath/dist/ $outputPublicDist");
        shell_exec("rsync -a $this->satisRepositoryOutputPath/meta/ $outputPublicMeta");

        $packageModel->debug_count = $packageModel->debug_count + 1;
        $packageModel->clone_status = Package::CLONE_STATUS_SUCCESS;
        $packageModel->is_cloned = 1;
        $packageModel->save();
    }

    public function failed($error)
    {
        dd($error);
        $packageModel = Package::where('id', $this->packageId)->first();

        $packageModel->clone_log = $error->getMessage();
        $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
        $packageModel->is_cloned = 0;
        $packageModel->save();
    }
}
