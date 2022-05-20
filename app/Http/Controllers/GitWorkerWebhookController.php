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

                if ($status == Package::REMOTE_CLONE_STATUS_FAILED) {
                    $findPackage->clone_status = Package::REMOTE_CLONE_STATUS_FAILED;
                    return $findPackage->save();
                }

                if ($status == Package::REMOTE_CLONE_STATUS_RUNNING) {
                    $findPackage->clone_status = Package::REMOTE_CLONE_STATUS_RUNNING;
                    return $findPackage->save();
                }

                $workerBuilds = storage_path() . '/package-manager-worker-builds/';
                $workerBuildsTemp = storage_path() . '/package-manager-worker-builds-temp/'.$signature.'/';
                if (!is_dir($workerBuildsTemp)) {
                    mkdir_recursive($workerBuildsTemp);
                }

                if ($status == Package::REMOTE_CLONE_STATUS_SUCCESS) {

                    $buildedZipPackage = $workerBuilds . $signature . '.zip';
                    if (is_file($buildedZipPackage)) {
                        $zip = new \ZipArchive();
                        if ($zip->open($buildedZipPackage) === TRUE) {

                            $zip->extractTo($workerBuildsTemp);
                            $zip->close();

                            // Maker rsync on another job
                            dispatch(new ProcessPackageSatisRsync([
                                'packageId'=>$findPackage->id,
                                'satisRepositoryOutputPath'=>$workerBuildsTemp
                            ]));

                            return ['done'=>true];

                        } else {
                            $findPackage->clone_log = "Can't open the builded zip file.";
                            $findPackage->clone_status = Package::REMOTE_CLONE_STATUS_FAILED;
                            return $findPackage->save();
                        }
                    }
                }
            }
        }
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
                            $findPackage->clone_status = Package::REMOTE_CLONE_STATUS_FAILED;
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

        $file = storage_path() . '/notif-' .  rand(111,999).'.txt';
        file_put_contents($file, json_encode($request->all(), JSON_PRETTY_PRINT));

    }
}
