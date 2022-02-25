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

        $repositoryName = false;

        // Github webhook
        if (isset($hookJson['repository']['full_name'])) {
            $repositoryName = $hookJson['repository']['full_name'];
        }

        // Gitlab webhook
        if (isset($hookJson['project']['path_with_namespace'])) {
            $repositoryName = $hookJson['project']['path_with_namespace'];
        }

        if ($repositoryName) {
            $findPackage = Package::where('name', $repositoryName)->first();
            if ($findPackage != null) {
                dispatch(new ProcessPackageSatis($findPackage->id));
                return ['success'=>true];
            }
        }

        return abort('404', 'Project not found');

    }
}
