<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Team;
use Illuminate\Http\Request;

class PackagesJsonController extends Controller
{
    public function index()
    {
        $json = [];
        $json['packages'] = [];

 /*       $getPackages = Package::where('clone_status', Package::CLONE_STATUS_SUCCESS)->get();
        if ($getPackages->count() > 0) {
            foreach ($getPackages as $package) {
                $package['package_json'] = str_replace('https://example.com/', config('app.url'), $package['package_json']);
                $packageContent = json_decode($package['package_json'],true);
                if (!empty($packageContent)) {
                    $json['packages'] = array_merge($packageContent, $json['packages']);
                }
            }
        }*/

        return $json;
    }

    public function team(Request $request, $slug = false) {

        if (!$slug) {
            return [];
        }

        $findTeam = Team::where('slug', $slug)
            ->with(['packages' => function ($query) {
                $query->where('clone_status', Package::CLONE_STATUS_SUCCESS);
            }])
            ->first();

        if ($findTeam == null) {
            return [];
        }

        $json = [];
        $json['packages'] = [];

        if ($findTeam->packages->count() > 0) {
            foreach ($findTeam->packages as $package) {
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
