<?php

namespace App\Http\Controllers;

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

                        $done = true;

                        $zip = new \ZipArchive();
                        if ($zip->open($buildedZipPackage) === TRUE) {

                            $zip->extractTo($workerBuildsTemp);
                            $zip->close();

                            $outputPublicDist = public_path() . '/dist/';
                            if (!is_dir($outputPublicDist)) {
                                mkdir($outputPublicDist, 0755, true);
                            }

                            $outputPublicMeta = public_path() . '/meta/';
                            if (!is_dir($outputPublicMeta)) {
                                mkdir($outputPublicMeta, 0755, true);
                            }

                            shell_exec("rsync -a $workerBuildsTemp/dist/ $outputPublicDist");
                            shell_exec("rsync -a $workerBuildsTemp/meta/ $outputPublicMeta");

                        } else {
                            $findPackage->clone_log = "Can't open the builded zip file.";
                        }

                        if ($done) {
                            $findPackage->clone_status = Package::REMOTE_CLONE_STATUS_SUCCESS;
                        } else {
                            $findPackage->clone_status = Package::REMOTE_CLONE_STATUS_FAILED;
                        }

                        return $findPackage->save();
                    }
                }
            }
        }
    }
}
