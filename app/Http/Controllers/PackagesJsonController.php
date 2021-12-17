<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackagesJsonController extends Controller
{
    public function index()
    {
        $json = [];
        $json['packages'] = [];

        $getPackages = Package::where('clone_status', Package::CLONE_STATUS_SUCCESS)->get();
        if ($getPackages->count() > 0) {
            foreach ($getPackages as $package) {
                $package['package_json'] = str_replace('https://example.com/', config('app.url'), $package['package_json']);
                $packageContent = json_decode($package['package_json'],true);
                if (!empty($packageContent)) {
                    $json['packages'] = array_merge($packageContent, $json['packages']);
                }
            }
        }

        return $json;
    }
}
