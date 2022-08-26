<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPackageSatisRsync;
use App\Models\Package;
use Illuminate\Http\Request;

class GitWorkerWebhookController extends Controller
{
    public function index(Request $request)
    {
        $signature = $request->get('signature', false);
        $status = $request->get('status', false);

        if ($signature) {
            $findPackage = Package::where('remote_build_signature', $signature)->first();
            if ($findPackage !== null) {

                if ($status == Package::CLONE_STATUS_FAILED) {
                    $findPackage->clone_status = Package::CLONE_STATUS_FAILED;
                    return $findPackage->save();
                }

                if ($status == Package::CLONE_STATUS_RUNNING) {
                    $findPackage->clone_status = Package::CLONE_STATUS_RUNNING;
                    return $findPackage->save();
                }

                $workerBuilds = storage_path() . '/package-manager-worker-builds/';
                $workerBuildsTemp = storage_path() . '/package-manager-worker-builds-temp/'.$signature.'/';
                if (!is_dir($workerBuildsTemp)) {
                    mkdir_recursive($workerBuildsTemp);
                }

                if ($status == Package::CLONE_STATUS_SUCCESS) {

                    $packageBuildZip = $workerBuilds . $signature . '.zip';
                    if (is_file($packageBuildZip)) {

                        // Maker rsync on another job
                        $job = new ProcessPackageSatisRsync([
                            'packageId'=>$findPackage->id,
                            'packageName'=>$findPackage->name,
                            'packageBuildZip'=>$packageBuildZip,
                            'satisRepositoryOutputPath'=>$workerBuildsTemp
                        ]);
                        dispatch($job)->onConnection('redis');

                        return ['done'=>true, 'rsync'=>'started', 'time'=>time()];
                    }
                }
            }
        }
        return ['none'=>true];
    }

    public function notification(Request $request)
    {
        $notification = $request->all();

        if (isset($notification['object_kind']) && isset($notification['object_attributes'])) {
            if ($notification['object_kind'] == 'pipeline') {
                if (isset($notification['object_attributes']['sha'])) {
                    $commitId = $notification['object_attributes']['sha'];
                    $findPackage = Package::where('remote_build_commit_id', $commitId)->first();
                    if ($findPackage != null) {
                        $status = $notification['object_attributes']['status'];
                        if ($status=='failed') {
                            $findPackage->clone_status = Package::CLONE_STATUS_FAILED;
                        }
                        if ($status=='canceled') {
                            $findPackage->clone_status = Package::CLONE_STATUS_FAILED;
                        }
                        if ($status=='pending') {
                            $findPackage->clone_status = Package::CLONE_STATUS_WAITING;
                        }
                        if ($status=='created') {
                            $findPackage->clone_status = Package::CLONE_STATUS_CLONING;
                        }
                        if ($status=='success') {
                            $findPackage->clone_log = 'Unzipping the builded package...';
                        }
                        $findPackage->clone_log = json_encode($notification, JSON_PRETTY_PRINT);
                        $findPackage->save();
                    }
                }
            }
        }

      /*  if (isset($notification['commit']['sha'])) {
            $commitId = $notification['commit']['sha'];
            $findPackage = Package::where('remote_build_commit_id', $commitId)->first();
            if ($findPackage != null) {
              //  dd($findPackage);
            }
        }*/

      //  $file = storage_path() . '/notif-' .  rand(111,999).'.txt';
    //    file_put_contents($file, json_encode($request->all(), JSON_PRETTY_PRINT));

    }
}
