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
                    $findPackage->status = Package::REMOTE_CLONE_STATUS_FAILED;
                    return $findPackage->save();
                }

                if ($status == Package::REMOTE_CLONE_STATUS_RUNNING) {
                    $findPackage->status = Package::REMOTE_CLONE_STATUS_RUNNING;
                    return $findPackage->save();
                }

                if ($status == Package::REMOTE_CLONE_STATUS_SUCCESS) {
                    $buildedZipPackage = storage_path() . '/package-manager-worker-builds/' . $signature . '.zip';
                    if (is_file($buildedZipPackage)) {


                        

                        $findPackage->status = Package::REMOTE_CLONE_STATUS_SUCCESS;
                        return $findPackage->save();
                    }
                }
            }
        }
    }
}
