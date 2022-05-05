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

class ProcessPackageSatis implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120 * 6;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = false;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 25;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 5;

    /**
     * The number of seconds after which the job will no longer stay unique.
     *
     * @var int
     */
    public $uniqueFor = 60;

    /**
     * @var int
     */
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
     * @return string
     */
    public function uniqueId()
    {
        return 'proc-pack-satis-' . $this->packageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
     //   \Artisan::call('queue:flush');

        $packageModel = Package::where('id', $this->packageId)
            ->with('credential')
            ->first();

        if ($packageModel->clone_status == Package::CLONE_STATUS_RUNNING) {
            // job already running
            $packageModel->clone_log = "Already running.";
            $packageModel->save();
            return;
        }

      //  Log::info($packageModel->toArray());

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
        $satisCommand[] = '-d memory_limit=-1 max_execution_time=6000';
        $satisCommand[] = '-c '.base_path().'/php.ini';
        $satisCommand[] = $satisBinPath;
        $satisCommand[] = 'build';
        $satisCommand[] = $saitsRepositoryPath . 'satis.json';
        $satisCommand[] = $satisRepositoryOutputPath;


        $composerCacheDir = base_path().'/composer-cache';
        if (!is_dir($composerCacheDir)) {
            mkdir($composerCacheDir);
        }

        $process = new Process($satisCommand,null,[
            'HOME'=>dirname(base_path()),
            'COMPOSER_CACHE_DIR '=>$composerCacheDir,
            'COMPOSER_MEMORY_LIMIT '=>'-1',
            'COMPOSER_PROCESS_TIMEOUT '=>100000,
            'COMPOSER_HOME'=>$saitsRepositoryPath
        ]);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
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

        $lastVersionMetaData = [];

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

                        if (isset($packageVersion['name'])) {
                            $lastVersionMetaData['name'] = $packageVersion['name'];
                        }

                        if (isset($packageVersion['type'])) {
                            $lastVersionMetaData['type'] = $packageVersion['type'];
                        }

                        if (isset($packageVersion['description'])) {
                            $lastVersionMetaData['description'] = $packageVersion['description'];
                        }

                        if (isset($packageVersion['keywords'])) {
                            $lastVersionMetaData['keywords'] = $packageVersion['keywords'];
                        }

                        if (isset($packageVersion['homepage'])) {
                            $lastVersionMetaData['homepage'] = $packageVersion['homepage'];
                        }

                        if (isset($packageVersion['version'])) {
                            $lastVersionMetaData['version'] = $packageVersion['version'];
                        }

                        if (isset($packageVersion['target-dir'])) {
                            $lastVersionMetaData['target_dir'] = $packageVersion['target-dir'];
                        }

                        if (isset($packageVersion['extra']['preview_url'])) {
                            $lastVersionMetaData['preview_url'] = $packageVersion['extra']['preview_url'];
                        }

                        if (isset($packageVersion['extra']['_meta']['screenshot'])) {
                            $lastVersionMetaData['screenshot'] = $packageVersion['extra']['_meta']['screenshot'];
                        }

                        if (isset($packageVersion['extra']['_meta']['readme'])) {
                            $lastVersionMetaData['readme'] = $packageVersion['extra']['_meta']['readme'];
                        }

                        $preparedPackageVerions[$packageVersionKey] = $packageVersion;
                    }
                    $preparedPackages[$packageKey] = $preparedPackageVerions;
                }
            }

            $foundedPackages = array_merge($foundedPackages, $preparedPackages);
        }

        $packageModel->debug_count = $packageModel->debug_count + 1;

        if (!empty($lastVersionMetaData)) {
            foreach ($lastVersionMetaData as $metaData=>$metaDataValue) {
                $packageModel->$metaData = $metaDataValue;
            }
        }

        $packageModel->package_json = json_encode($foundedPackages,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $packageModel->clone_log = $output;
        $packageModel->save();

        // Maker rsync on another job
        dispatch_sync(new ProcessPackageSatisRsync([
            'packageId'=>$packageModel->id,
            'satisRepositoryOutputPath'=>$satisRepositoryOutputPath
        ]));
    }

    public function failed($error)
    {
        dd($error);
        $packageModel = Package::where('id', $this->packageId)->first();
        $packageModel->clone_log = $error->getMessage();
        $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
        $packageModel->save();
    }
}
