<?php

namespace App\Jobs;


use App\Helpers\PackageManagerGitWorker;
use App\Helpers\SatisHelper;
use App\SatisPackageBuilder;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\Helpers;

use App\Models\Credential;
use App\Models\Package;
use App\Helpers\RepositoryPathHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPackageSatis implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 320 * 6;

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
    public $tries = 100;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 6;

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
     * @var string
     */
    public $packageName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($packageId, $packageName)
    {
        $this->packageId = $packageId;
        $this->packageName = $packageName;
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
        $packageModel = Package::where('id', $this->packageId)
            ->with('credential')
            ->first();

        if ($packageModel->clone_status == Package::CLONE_STATUS_RUNNING) {
            // job already running
            $packageModel->clone_log = "Already running.";
            $packageModel->save();
            return;
        }

        $signature = md5($packageModel->id . time() . rand(111, 999));
        $callbackUrl = route('git-worker-webhook');

        $packageModel->remote_build_signature = $signature;
        $packageModel->clone_log = "Job is started.";
        $packageModel->clone_status = Package::CLONE_STATUS_RUNNING;
        $packageModel->save();

        $isPrivateRepository = SatisHelper::checkRepositoryIsPrivate($packageModel->repository_url);

        $satisContent = [
            'name' => 'microweber/packages',
            'homepage' => 'https://example.com',
            'repositories' => [
                [
                    'type' => 'git',
                    'url' => $packageModel->repository_url,
                ]
            ],
            'require-all' => true,
            "archive" => [
                "directory" => "dist",
                "format" => "zip",
                "skip-dev" => true,
                //"checksum"=> false
            ],
            "config" => [
                "disable-tls" => true,
            ]
        ];

        $preferredInstall = 'dist';
        if ($isPrivateRepository) {
            $preferredInstall = 'source';
        }
        $satisContent['config']['properties'] = [
            "preferred-install" => [
                "*" => $preferredInstall
            ],
        ];


        if ($isPrivateRepository) {
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
        }

        // Satis json
        $satisJson = json_encode($satisContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $saitsRepositoryPath = RepositoryPathHelper::getRepositoriesSatisPath($packageModel->id);
        $satisFile = $saitsRepositoryPath . 'satis.json';
        file_put_contents($satisFile, $satisJson);

        // Build settings json
        $buildSettingsJson = [
            "runner_config" => [
                "signature" => $signature,
                "callback_url" => $callbackUrl
            ]
        ];
        $buildSettingsJson = json_encode($buildSettingsJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $buildSettingsFile = $saitsRepositoryPath . 'build-settings.json';
        file_put_contents($buildSettingsFile, $buildSettingsJson);

        if (env('PACKAGE_MANAGER_WORKER_TYPE') == 'github' || env('PACKAGE_MANAGER_WORKER_TYPE') == 'gitlab') {

            $response = PackageManagerGitWorker::pushSatis($satisFile, $buildSettingsFile);
            if (!$response['commit_id']) {

                $packageModel->clone_log = "Can't git push.";
                $packageModel->clone_status = Package::CLONE_STATUS_FAILED;

                return $packageModel->save();
            }

            $packageModel->remote_build_commit_id = $response['commit_id'];
            return $packageModel->save();
        }

        $status = SatisPackageBuilder::build($satisFile);

        $packageJsonContent = file_get_contents($status['output_path'] . DIRECTORY_SEPARATOR . 'packages.json');
        $packageJsonContent = json_decode($packageJsonContent, true);

        if (empty($packageJsonContent)) {

            $packageModel->clone_log = 'Failed to open package json content';
            $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
            $packageModel->save();

            throw new \Exception('Can\'t open package json content');
        }

        $packageModel->debug_count = $packageModel->debug_count + 1;

        /* if (!empty($lastVersionMetaData)) {
             foreach ($lastVersionMetaData as $metaData=>$metaDataValue) {
                 $packageModel->$metaData = $metaDataValue;
             }
         }*/

        $packageModel->package_json = json_encode($packageJsonContent['packages'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $packageModel->clone_log = 'done!';
        $packageModel->clone_status = Package::CLONE_STATUS_SUCCESS;
        $packageModel->save();
        
        // Maker rsync on another job
        dispatch_sync(new ProcessPackageSatisRsync([
            'packageId' => $packageModel->id,
            'packageName' => $packageModel->name,
            'satisRepositoryOutputPath' => $status['output_path']
        ]));
    }

     public function failed($error)
     {
         $packageModel = Package::where('id', $this->packageId)->first();
         $packageModel->clone_log = $error->getMessage();
         $packageModel->clone_status = Package::CLONE_STATUS_FAILED;
         $packageModel->save();
     }
}
