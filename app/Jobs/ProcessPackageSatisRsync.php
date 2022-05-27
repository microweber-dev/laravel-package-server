<?php

namespace App\Jobs;

use App\Helpers\RepositoryMediaProcessHelper;
use App\Helpers\SatisHelper;
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
    public $packageName;
    public $satisRepositoryOutputPath;
    public $packageBuildZip;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->packageId = $params['packageId'];
        $this->packageName = $params['packageName'];
        $this->satisRepositoryOutputPath = $params['satisRepositoryOutputPath'];

        if (isset($params['packageBuildZip'])) {
            $this->packageBuildZip = $params['packageBuildZip'];
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $packageModel = Package::where('id', $this->packageId)->first();

        if ($this->packageBuildZip) {

            $zip = new \ZipArchive();
            if ($zip->open($this->packageBuildZip) === TRUE) {
                $zip->extractTo($this->satisRepositoryOutputPath);
                $zip->close();
            } else {
                $packageModel->clone_log = "Can't open the builded zip file.";
                $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
                return $packageModel->save();
            }
        }

        $outputPublicDist = public_path() . '/dist/';
        if (!is_dir($outputPublicDist)) {
            mkdir($outputPublicDist, 0755, true);
        }

        $outputPublicMeta = public_path() . '/meta/';
        if (!is_dir($outputPublicMeta)) {
            mkdir($outputPublicMeta, 0755, true);
        }

        $packageJson = file_get_contents($this->satisRepositoryOutputPath.'/packages.json');
        $packageJson = json_decode($packageJson, true);


        $latestVersion = SatisHelper::getLatestVersionFromPackage($packageJson['packages']);
        $latestVersionMetaData = SatisHelper::getMetaDataFromPackageVersion($latestVersion);

        if (!empty($latestVersionMetaData)) {
            foreach ($latestVersionMetaData as $metaData=>$metaDataValue) {
                $packageModel->$metaData = $metaDataValue;
            }
        }

        $packageModel->package_json = json_encode($packageJson['packages'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

        shell_exec("rsync -a ".$this->satisRepositoryOutputPath."/dist/ $outputPublicDist");
        shell_exec("rsync -a ".$this->satisRepositoryOutputPath."/meta/ $outputPublicMeta");

        rmdir_recursive($this->satisRepositoryOutputPath);

        if ($this->packageBuildZip) {
            @unlink($this->packageBuildZip);
        }

    //    $packageModel->debug_count = $packageModel->debug_count + 1;
        $packageModel->clone_status = Package::CLONE_STATUS_SUCCESS;
        $packageModel->is_cloned = 1;
        $packageModel->save();
    }

   /* public function failed($error)
    {
        $packageModel = Package::where('id', $this->packageId)->first();

        $packageModel->clone_log = $error->getMessage();
        $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
        $packageModel->is_cloned = 0;
        $packageModel->save();
    }*/
}
