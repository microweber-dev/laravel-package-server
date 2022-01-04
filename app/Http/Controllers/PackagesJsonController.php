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

        if ($findTeam->isPrivate()) {

            $logged = false; 

            //check if request has authorization header
            if ($request->header('PHP_AUTH_USER', null) && $request->header('PHP_AUTH_PW', null)) {

                $username = $request->header('PHP_AUTH_USER');
                $password = $request->header('PHP_AUTH_PW');

                if ($username === 'token' && $password === $findTeam->token) {
                    $logged = true;
                }
            }

            //user not logged, request authentication
            if ($logged === false) {
                $headers = ['WWW-Authenticate' => 'Basic'];
                return response()->make('Invalid credentials.', 401, $headers);
            }
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
