<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageDownloadStats;
use App\Models\Team;
use App\Models\TeamPackage;
use Illuminate\Database\Eloquent\Builder;
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

    public function downloadNotify(Request $request) {

        $data = [];
        $data['request'] = $request->all();
        $data['headers'] = collect($request->header())->transform(function ($item) {
            return $item[0];
        });
        $data['ip_address'] = $request->ip();

        if (isset($data['request']['downloads'])) {
            foreach ($data['request']['downloads'] as $download) {

                $findPackageByName = Package::where('name', $download['name'])->first();
                if ($findPackageByName != null) {

                    $downloadStats = new PackageDownloadStats();
                    $downloadStats->package_id = $findPackageByName->id;
                    $downloadStats->name = $download['name'];
                    $downloadStats->version = $download['version'];
                    $downloadStats->ip_address = $data['ip_address'];
                    $downloadStats->authorization = $data['headers']['authorization'];
                    $downloadStats->host = $data['headers']['host'];
                    $downloadStats->user_agent = $data['headers']['user-agent'];
                    $downloadStats->stats_hour = date('H');
                    $downloadStats->stats_day = date('d');
                    $downloadStats->stats_month = date('m');
                    $downloadStats->stats_year = date('Y');
                    $downloadStats->save();

                }
            }
        }

    }

    public function team(Request $request, $slug = false) {

        ini_set('memory_limit', '512M');

        if (!$slug) {
            return [];
        }

        $findTeam = Team::where('slug', $slug)->first();
        if ($findTeam == null) {
            return [];
        }

        $teamPackages = TeamPackage::where('team_id', $findTeam->id)
            ->whereHas('package', function (Builder $query) {
                $query->where('clone_status',Package::CLONE_STATUS_SUCCESS);
            })
            ->where('is_visible', 1)
            ->with('package')
            ->get();

        if ($teamPackages == null) {
            return [];
        }

        $teamSettings = $findTeam->settings()->get();

        $logged = false;
        if ($findTeam->isPrivate()) {

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

        if ($teamPackages->count() > 0) {
            foreach ($teamPackages as $teamPackage) {

                $package = $teamPackage->package;
                $packageJson = $package->package_json;

                $packageJson = str_replace('https://example.com/', config('app.url'), $packageJson);
                $packageContent = json_decode($packageJson,true);
                if (!empty($packageContent)) {
                    foreach ($packageContent as $packageName=>$packageVersions) {
                        $json['packages'][$packageName] = $this->_prepareVersions($packageVersions,[
                            'token_authenticated'=>$logged,
                            'whmcs_product_ids'=>$teamPackage->whmcs_product_ids,
                            'is_visible'=>$teamPackage->is_visible,
                            'is_paid'=>$teamPackage->is_paid,
                            'team_settings'=>$teamSettings
                        ]);
                    };
                }
            }
        }

        return $json;

    }

    private function _prepareVersions($versions, $teamPackage) {

        $prepareVersions = [];
        foreach ($versions as $version=>$package) {
            $prepareVersions[$version] = $this->_preparePackage($package, $teamPackage);
        }

        return $prepareVersions;
    }

    private function _preparePackage($package, $teamPackage) {

        if (isset($package['extra']['preview_url'])) {
            if (isset($teamPackage['team_settings']['package_manager_templates_demo_domain'])) {

                $previewUrl = $package['extra']['preview_url'];
                $previewUrl = str_replace('templates.microweber.com', $teamPackage['team_settings']['package_manager_templates_demo_domain'], $previewUrl);

                $package['extra']['preview_url'] = $previewUrl;
            }
        }

        $package['notification-url'] = route('packages.download-notify');

        $whmcsUrl = '';
        if (isset($teamPackage['team_settings']['whmcs_url'])) {
            $whmcsUrl = $teamPackage['team_settings']['whmcs_url'];
        }
        $package['extra']['whmcs']['whmcs_url'] = $whmcsUrl;

        if (isset($teamPackage['is_paid']) && $teamPackage['is_paid'] == 1) {

            if (isset($teamPackage['whmcs_product_ids']) && !empty($teamPackage['whmcs_product_ids'])) {

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
                                if ($this->_validateLicenseKey($whmcsUrl, $userLicenseKey)) {
                                    $licensed = true;
                                }
                            }
                        }
                    }

                }

                if (isset($teamPackage['token_authenticated']) && $teamPackage['token_authenticated'] === true) {
                    $licensed = true;
                }

                if (!$licensed) {
                    $package['dist'] = [
                        "type" => "license_key",
                        "url" => $whmcsUrl,
                        "reference" => "license_key",
                        "shasum" => "license_key"
                    ];
                }

                $package['license_ids'] = $teamPackage['whmcs_product_ids'];
                $package['extra']['whmcs']['whmcs_product_ids'] = $teamPackage['whmcs_product_ids'];

            }
        }

        return $package;
    }

    private function _validateLicenseKey($whmcsUrl, $key) {

        $checkWhmcs = file_get_contents($whmcsUrl . '/index.php?m=microweber_addon&function=validate_license&license_key=' . $key);
        $checkWhmcs = json_decode($checkWhmcs, TRUE);
        if (isset($checkWhmcs['status']) && $checkWhmcs['status'] == 'success') {
            return true;
        }

        return false;
    }

}
