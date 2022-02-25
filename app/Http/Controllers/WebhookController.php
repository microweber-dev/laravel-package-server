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

        if (isset($hookJson['project']['web_url'])) {
            $findPackage = Package::where('repository_url', $hookJson['project']['web_url'])->first();
            if ($findPackage != null) {
                dispatch(new ProcessPackageSatis($findPackage->id));
                return ['success' => true];
            }
        }

        // Github webhook
        if (isset($hookJson['repository']['full_name'])) {
            $findPackage = Package::where('name', $hookJson['repository']['full_name'])->first();
            if ($findPackage != null) {
                dispatch(new ProcessPackageSatis($findPackage->id));
                return ['success' => true];
            }
        }

        return abort('404', 'Project not found');

    }
}
