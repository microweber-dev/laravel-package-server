<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPackageSatis;
use App\Models\Package;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        $hookJson = $request->json()->all();


        $repoUrl = '';


        if (isset($hookJson['repository']['html_url'])) {
            $repoUrl = $hookJson['repository']['html_url'];
        }
//        if (isset($hookJson['repository']['url'])) {
//            $repoUrl = $hookJson['repository']['url'];
//        } elseif (isset($hookJson['project']['web_url'])) {
//            $repoUrl = $hookJson['project']['web_url'];
//        } elseif (isset($hookJson['html_url'])) {
//            $repoUrl = $hookJson['html_url'];
//        }
//        return ['url' => $repoUrl];
//        // Gitlab
        if ($repoUrl) {
            $findPackage = Package::where('repository_url', $repoUrl)
                ->orWhere('repository_url', 'LIKE' . '%' . $repoUrl . '%')
                ->first();
            if ($findPackage != null) {
                dispatch(new ProcessPackageSatis($findPackage->id, $findPackage->name));
                return ['success' => true];
            }
        }

        // Github webhook
        if (isset($hookJson['repository']['full_name'])) {
            $findPackage = Package::where('name', $hookJson['repository']['full_name'])->first();
            if ($findPackage != null) {
                dispatch(new ProcessPackageSatis($findPackage->id, $findPackage->name));
                return ['success' => true];
            }
        }

        return abort('404', 'Project not found');

    }
}
