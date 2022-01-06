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

                    foreach ($json['packages'] as $packageName=>$packageVersions) {
                        $json['packages'][$packageName] = $this->_prepareVersions($packageVersions);
                    }

                    $json['packages'] = array_merge($packageContent, $json['packages']);
                }
            }
        }

        return $json;

    }

    private function _prepareVersions($versions) {

        $prepareVersions = [];
        foreach ($versions as $version=>$package) {
            $prepareVersions[$version] = $this->_preparePackage($package);
        }

        return $prepareVersions;
    }

    private function _preparePackage($package) {


        if (isset($package['extra']['preview_url'])) {
            if (true) {

                $previewUrl = $package['extra']['preview_url'];
                $previewUrl = str_replace('templates.microweber.com', 'package_manager_templates_demo_domain', $previewUrl);

                $package['extra']['preview_url'] = $previewUrl;
            }
        }

        $package['extra']['whmcs']['whmcs_url'] = 'WHMCS URL';

        $packageUrl = $this->_clearRepositoryUrl($package['source']['url']);

        if (isset($this->repositories[$packageUrl])) {
            $repositorySettings = $this->repositories[$packageUrl];
            //$repositorySettings['whmcs_product_ids'] = 1;
            if (isset($repositorySettings['whmcs_product_ids']) && !empty($repositorySettings['whmcs_product_ids'])) {

                $licensed = false;
                file_put_contents(base_path().'/server.txt', print_r($_SERVER,1));

                if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])){
                    $_SERVER["HTTP_AUTHORIZATION"] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                }

                if (isset($_SERVER["HTTP_AUTHORIZATION"]) && (strpos(strtolower($_SERVER["HTTP_AUTHORIZATION"]),'basic') !== false)) {

                    ///  file_put_contents(base_path().'/lic.txt', print_r((substr($_SERVER["HTTP_AUTHORIZATION"], 6)),1));

                    $userLicenseKeys = base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6));

                    if(is_string($userLicenseKeys) and (strpos(strtolower($userLicenseKeys),'license:') !== false)){
                        $userLicenseKeys =  substr($userLicenseKeys, 8);
                        $userLicenseKeys =  base64_decode($userLicenseKeys);
                    }

                    $userLicenseKeysJson = json_decode($userLicenseKeys, true);

                    $userLicenseKeysForValidation = [];
                    // old method read
                    if (!empty($userLicenseKeysJson)) {
                        $userLicenseKeysForValidation = $userLicenseKeysJson;
                    } else {
                        // when is not empty
                        $userLicenseKeysForValidation[]['local_key'] = $userLicenseKeys;
                    }

                    $userLicenseKeysMap = [];
                    if ($userLicenseKeysForValidation && !empty($userLicenseKeysForValidation) && is_array($userLicenseKeysForValidation)) {
                        foreach ($userLicenseKeysForValidation as $userLicenseKey) {
                            if (isset($userLicenseKey['local_key'])) {
                                $userLicenseKeysMap[] = $userLicenseKey['local_key'];
                            }
                        }

                        if (!empty($userLicenseKeysMap)) {
                            foreach ($userLicenseKeysMap as $userLicenseKey) {
                                if ($this->_validateLicenseKey($userLicenseKey)) {
                                    $licensed = true;
                                }
                            }
                        }
                    }

                }

                if (!$licensed) {
                    $package['dist'] = [
                        "type" => "license_key",
                        "url" => $this->whmcs_url,
                        "reference" => "license_key",
                        "shasum" => "license_key"
                    ];
                }

                $package['license_ids'] = $repositorySettings['whmcs_product_ids'];
                $package['extra']['whmcs']['whmcs_product_ids'] = $repositorySettings['whmcs_product_ids'];

            }
        }

        return $package;
    }

    private function _validateLicenseKey($key) {

        $checkWhmcs = file_get_contents($this->whmcs_url . '/index.php?m=microweber_addon&function=validate_license&license_key=' . $key);
        $checkWhmcs = json_decode($checkWhmcs, TRUE);
        if (isset($checkWhmcs['status']) && $checkWhmcs['status'] == 'success') {
            return true;
        }

        return false;
    }

    private function _clearRepositoryUrl($repositoryUrl) {

        $repositoryUrl = str_replace('https://', false, $repositoryUrl);
        $repositoryUrl = str_replace('http://', false, $repositoryUrl);
        $repositoryUrl = str_replace('http://wwww.', false, $repositoryUrl);
        $repositoryUrl = str_replace('https://wwww.', false, $repositoryUrl);
        $repositoryUrl = str_replace(':', '/', $repositoryUrl);
        $repositoryUrl = str_replace('git@', '', $repositoryUrl);

        return $repositoryUrl;
    }

}
