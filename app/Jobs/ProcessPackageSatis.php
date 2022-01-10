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
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symplify\GitWrapper\GitWrapper;

class ProcessPackageSatis implements ShouldQueue
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
        \Artisan::call('queue:flush');

        $packageModel = Package::where('id', $this->packageId)->with('credential')->first();

        $packageModel->clone_log = "Job is started.";
        $packageModel->clone_status = Package::CLONE_STATUS_RUNNING;
        $packageModel->save();

        $satisContent = [
            'name'=>'microweber/packages',
            'homepage'=>'https://example.com',
            'repositories'=>[
                [
                    'type'=>'vcs',
                    'url'=> $packageModel->repository_url,
                ]
            ],
            'require-all'=> true,
             "archive" => [
                "directory"=> "dist",
                "format"=> "zip",
                "skip-dev"=> true
            ],
        ];

        if ($packageModel->credential !== null) {
            if ($packageModel->credential->authentication_type == Credential::TYPE_GITLAB_TOKEN) {
                if (isset($packageModel->credential->authentication_data['accessToken'])) {
                    $satisContent['config']['gitlab-oauth'] = [
                        $packageModel->credential->domain => $packageModel->credential->authentication_data['accessToken']
                    ];
                }
            }
            if ($packageModel->credential->authentication_type == Credential::TYPE_GITHUB_OAUTH) {
                if (isset($packageModel->credential->authentication_data['accessToken'])) {
                    $satisContent['config']['github-oauth'] = [
                        $packageModel->credential->domain => $packageModel->credential->authentication_data['accessToken']
                    ];
                }
            }
        }

        $satisJson = json_encode($satisContent, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $saitsRepositoryPath = RepositoryPathHelper::getRepositoriesSatisPath($packageModel->id);

        file_put_contents($saitsRepositoryPath . 'satis.json', $satisJson);

        $satisBinPath = base_path() . '/satis-builder/vendor/composer/satis/bin/satis';
        $satisRepositoryOutputPath = $saitsRepositoryPath . 'output-build';

        // Accept host key
        $parseRepositoryUrl = $packageModel->repository_url;
        $parseRepositoryUrl = parse_url($parseRepositoryUrl);
        if (isset($parseRepositoryUrl['host'])) {
            $hostname = $parseRepositoryUrl['host'];
            $acceptHost = shell_exec('
            if ! grep "$(ssh-keyscan '.$hostname.' 2>/dev/null)" ~/.ssh/known_hosts > /dev/null; then
                ssh-keyscan '.$hostname.' >> ~/.ssh/known_hosts
            fi');
        }

        if (!is_dir($saitsRepositoryPath)) {
            mkdir($saitsRepositoryPath);
        }

        $satisCommand = [];
        $satisCommand[] = 'php';
        $satisCommand[] = '-d memory_limit=-1 max_execution_time=600';
        //$satisCommand[] = '-c '.base_path().'/php.ini';  
        $satisCommand[] = $satisBinPath;
        $satisCommand[] = 'build';
        $satisCommand[] = $saitsRepositoryPath . 'satis.json';
        $satisCommand[] = $satisRepositoryOutputPath;

        $process = new Process($satisCommand,null,[
            'HOME'=>dirname(base_path()),
            'COMPOSER_HOME'=>$saitsRepositoryPath
        ]);
        $process->mustRun();
        $output = $process->getOutput();

        $packagesJsonFilePath = $satisRepositoryOutputPath . '/packages.json';
        if (!is_file($packagesJsonFilePath)) {
            throw new \Exception('Build failed. packages.json missing.');
        }

        $packagesJson = json_decode(file_get_contents($packagesJsonFilePath),true);
        if (empty($packagesJson)) {
            if (!is_file($packagesJsonFilePath)) {
                throw new \Exception('Build failed. packages.json is empty.');
            }
        }

        $includedPackageFiles = [];
        foreach($packagesJson['includes'] as $includeKey=>$includes) {
            $includedPackageFiles[] = $satisRepositoryOutputPath .'/'. $includeKey;
        }

        $foundedPackages = [];
        foreach($includedPackageFiles as $file) {

            $includedPackageContent = json_decode(file_get_contents($file), true);

            $preparedPackages = [];
            if ( !empty($includedPackageContent['packages'])) {
                foreach ($includedPackageContent['packages'] as $packageKey=>$packageVersions) {
                    $preparedPackageVerions = [];
                    foreach ($packageVersions as $packageVersionKey=>$packageVersion) {
                        if (strpos($packageVersionKey, 'dev') !== false) {
                            continue;
                        }
                        $packageVersion = RepositoryMediaProcessHelper::preparePackageMedia($packageVersion, $satisRepositoryOutputPath);
                        $preparedPackageVerions[$packageVersionKey] = $packageVersion;
                    }
                    $preparedPackages[$packageKey] = $preparedPackageVerions;
                }
            }

            $foundedPackages = array_merge($foundedPackages, $preparedPackages);
        }

        $outputPublicDist = public_path() . '/dist/';
        if (!is_dir($outputPublicDist)) {
            mkdir($outputPublicDist, 0755, true);
        }

        $outputPublicMeta = public_path() . '/meta/';
        if (!is_dir($outputPublicMeta)) {
            mkdir($outputPublicMeta, 0755, true);
        }

        shell_exec("rsync -avzh  $satisRepositoryOutputPath/dist/ $outputPublicDist");
        shell_exec("rsync -avzh  $satisRepositoryOutputPath/meta/ $outputPublicMeta");

        $packageModel->package_json = json_encode($foundedPackages,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $packageModel->clone_log = $output;
        $packageModel->clone_status = Package::CLONE_STATUS_SUCCESS;
        $packageModel->is_cloned = 1;
        $packageModel->save();
    }

    public function failed($error)
    {
        $packageModel = Package::where('id', $this->packageId)->first();

        $packageModel->clone_log = $error->getMessage();
        $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
        $packageModel->save();
    }
}
