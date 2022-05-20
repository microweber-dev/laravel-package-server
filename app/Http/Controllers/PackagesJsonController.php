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
    public function index(Request $request)
    {
        $host = $request->getHost();
        $findTeam = Team::where('domain', $host)->first();

        if ($findTeam == null) {
            return [];
        }

        return $this->getTeamPackages($findTeam->id);
    }

    public function downloadNotify(Request $request)
    {
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

                    $downloadStats->save();

                }
            }
        }

    }

    public function team(Request $request, $slug = false)
    {
        if (!$slug) {
            return [];
        }

        $findTeam = Team::where('slug', $slug)->first();
        if ($findTeam == null) {
            return [];
        }

        return $this->getTeamPackages($findTeam->id);
    }

    public function singlePackage($vendor, $package, Request $request)
    {
        $packageName = $vendor . '/' . $package;
        $findPackageByName = Package::where('name', $packageName)->first();
        if ($findPackageByName != null) {

            $host = $request->getHost();
            $findTeam = Team::where('domain', $host)->first();

            if ($findTeam == null) {
                return [];
            }

            return $this->getTeamPackages($findTeam->id, ['package_id'=>$findPackageByName->id]);

        }
    }

    protected function getTeamPackages($teamId, $filter = [])
    {
        ini_set('memory_limit', '512M');

        $request = request();

        $findTeam = Team::where('id', $teamId)->first();
        if ($findTeam == null) {
            return [];
        }

        $teamPackages = TeamPackage::where('team_id', $findTeam->id)
            ->whereHas('package', function (Builder $query) use ($filter) {
                if (isset($filter['package_id'])) {
                    $query->where('id', $filter['package_id']);
                }
                $query->whereNotIn('clone_status', [Package::CLONE_STATUS_FAILED,Package::REMOTE_CLONE_STATUS_FAILED]);
            })
            ->where('is_visible', 1)
            ->orderBy('position','asc')
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

        $authHeader = $request->header('authorization', false);
        if ($authHeader) {
            $authDecode = str_replace('Basic','', $authHeader);
            $authDecode = trim($authDecode);
            $authDecode = base64_decode($authDecode);

            if (str_contains($authDecode, 'plesk|')) {
                // Request from plesk
                $authDecodeLicenses = json_decode($authDecode, true);
                if (!empty($authDecodeLicenses)) {
                    foreach ($authDecodeLicenses as $decodeLicense) {
                        if (str_contains($decodeLicense, 'plesk|')) {
                            $decodeLicense = str_replace('plesk|', false, $decodeLicense);
                            $decodeLicenseData = json_decode(base64_decode($decodeLicense), true);
                            if (isset($decodeLicenseData['lim_date'])
                                && isset($decodeLicenseData['active'])
                                && $decodeLicenseData['active'] == true) {
                                $logged = true;
                            }
                        }
                    }
                }
            }
        }

        $headers = collect($request->header())->transform(function ($item) {
            return $item[0];
        });

        $composerRequest = false;
        if (isset($headers['user-agent'])) {
            if (stripos($headers['user-agent'], 'Composer') !== false) {
                $composerRequest = true;
            }
        }

        $json = [];
        $json['packages'] = [];

        $yml = [];
        $format = $request->get('format', false);

        if ($teamPackages->count() > 0) {
            foreach ($teamPackages as $teamPackage) {

                $package = $teamPackage->package;
                $packageJson = $package->package_json;

                $packageJson = str_replace('https://example.com/', config('app.url'), $packageJson);
                $packageContent = json_decode($packageJson, true);
                if (!empty($packageContent)) {
                    foreach ($packageContent as $packageName => $packageVersions) {
                        $json['packages'][$packageName] = $this->_prepareVersions($packageVersions, [
                            'token_authenticated' => $logged,
                            'whmcs_primary_product_id' => $teamPackage->whmcs_primary_product_id,
                            'whmcs_product_ids' => $teamPackage->getWhmcsProductIds(),
                            'is_visible' => $teamPackage->is_visible,
                            'is_paid' => $teamPackage->is_paid,
                            'buy_url' => $teamPackage->buy_url,
                            'buy_url_from' => $teamPackage->buy_url_from,
                            'composer_request' => $composerRequest,
                            'team_settings' => $teamSettings
                        ]);
                        if (strpos($packageName, 'template') !== false) {
                            $yml[] = $packageName;
                        }
                    };
                }
            }
        }

        if ($format == 'yml') {
            return json_encode($yml, JSON_PRETTY_PRINT);
        }

        return $json;

    }

    private function _prepareVersions($versions, $teamPackage)
    {

        $prepareVersions = [];
        foreach ($versions as $version => $package) {
            $preparedPackage = $this->_preparePackage($package, $teamPackage);

            if($preparedPackage['dist']['type'] == 'license_key') {
                if (isset($teamPackage['composer_request']) && $teamPackage['composer_request']) {
                    continue;
                }
            }

            $prepareVersions[$version] = $preparedPackage;
        }

        return $prepareVersions;
    }

    private function _preparePackage($package, $teamPackage)
    {

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

                if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                    $_SERVER["HTTP_AUTHORIZATION"] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                }

                if (isset($_SERVER["HTTP_AUTHORIZATION"]) && (strpos(strtolower($_SERVER["HTTP_AUTHORIZATION"]), 'basic') !== false)) {

                    ///  file_put_contents(base_path().'/lic.txt', print_r((substr($_SERVER["HTTP_AUTHORIZATION"], 6)),1));

                    $userLicenseKeys = base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6));

                    if (is_string($userLicenseKeys) and (strpos(strtolower($userLicenseKeys), 'license:') !== false)) {
                        $userLicenseKeys = substr($userLicenseKeys, 8);
                        $userLicenseKeys = base64_decode($userLicenseKeys);
                    }

                    $userLicenseKeysJson = json_decode($userLicenseKeys, true);

                    $userLicenseKeysForValidation = [];
                    // old method read
                    if (!empty($userLicenseKeysJson)) {
                        $userLicenseKeysForValidation = $userLicenseKeysJson;
                    } else {
                        // when is not empty
                        if ($userLicenseKeys and trim($userLicenseKeys) != '' and $userLicenseKeys != '[]') {
                            $userLicenseKeysForValidation[]['local_key'] = $userLicenseKeys;

                        }
                    }

                    $userLicenseKeysMap = [];
                    if ($userLicenseKeysForValidation && !empty($userLicenseKeysForValidation) && is_array($userLicenseKeysForValidation)) {
                        foreach ($userLicenseKeysForValidation as $userLicenseKey) {
                            if (isset($userLicenseKey['local_key']) and trim($userLicenseKey['local_key']) != '') {
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

                // Default buy link from first whmcs product id
                $whmcProductId = $teamPackage['whmcs_product_ids'][0];
               // $package['extra']['whmcs']['add_to_cart_link'] = $whmcsUrl . '/cart.php?a=add&pid=' . $whmcProductId;
                $package['extra']['whmcs']['buy_link'] = $whmcsUrl . '/cart.php?a=add&pid=' . $whmcProductId;

                // Primary product id buy link
                if ($teamPackage['whmcs_primary_product_id'] > 0) {
                    $whmcProductId = $teamPackage['whmcs_primary_product_id'];
                  //  $package['extra']['whmcs']['add_to_cart_link'] = $whmcsUrl . '/cart.php?a=add&pid=' . $whmcProductId;
                    $package['extra']['whmcs']['buy_link'] = $whmcsUrl . '/cart.php?a=add&pid=' . $whmcProductId;
                }

                if ($teamPackage['buy_url_from'] == 'custom') {
                    $package['extra']['whmcs']['buy_link'] = $teamPackage['buy_url'];
                }

            }
        }

        return $package;
    }

    public static $key_status_check_cache = [];
    private function _validateLicenseKey($whmcsUrl, $key)
    {

//        $checkWhmcs = file_get_contents($whmcsUrl . '/index.php?m=microweber_addon&function=validate_license&license_key=' . $key);
//        $checkWhmcs = json_decode($checkWhmcs, TRUE);

        if(isset(self::$key_status_check_cache[$key])){
            return self::$key_status_check_cache[$key];
        }

        $checkWhmcs = $this->_validateLicenseMakeRequest($whmcsUrl,$key);
        if (isset($checkWhmcs['status']) && $checkWhmcs['status'] == 'success') {
            self::$key_status_check_cache[$key] = true;
            return true;
        }
        self::$key_status_check_cache[$key] = false;
        return false;
    }


    private function _validateLicenseMakeRequest($whmcsUrl,$key)
    {
        $curl = curl_init();


        $checkWhmcsUrl = ($whmcsUrl . '/index.php?m=microweber_addon&function=validate_license&license_key=' . $key);

        $opts = [
            CURLOPT_URL => $checkWhmcsUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",

        ];

        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
           // return ["error" => "cURL Error #:" . $err];
            return [];
        } else {
            $getResponse = json_decode($response, true);

            if (isset($getResponse['status'])) {
                return $getResponse;
            }
            return [];
        }
    }
}
