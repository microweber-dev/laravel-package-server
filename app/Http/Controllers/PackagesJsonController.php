<?php

namespace App\Http\Controllers;

use App\Helpers\StringHelper;
use App\Helpers\WhmcsLicenseValidatorHelper;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Package;
use App\Models\PackageDownloadStats;
use App\Models\Team;
use App\Models\TeamPackage;
use App\Models\WhmcsServer;
use Carbon\Carbon;
use DarthSoup\Whmcs\Facades\Whmcs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PackagesJsonController extends Controller
{
    public function index(Request $request)
    {
        $host = $request->getHost();
        $findTeam = Team::where('domain', $host)->first();

        if ($findTeam == null) {
            return [];
        }

        return $this->getTeamPackages($request, $findTeam->id);
    }

    public function downloadPrivatePackage(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }
        if (request()->ip() !== $request->get('ip')) {
            abort(401,'Invalid authorization.');
        }

        $requestHeaders = collect($request->header())->transform(function ($item) {
            return $item[0];
        });

        $licenseIds = $request->get('license_ids', false);
        $licenseIds = base64_decode($licenseIds);
        $licenseIds = json_decode($licenseIds, TRUE);

        if (!empty($licenseIds)) {
            foreach ($licenseIds as $licenseId) {

                $findLicense = License::where('id', $licenseId)->first();
                if ($findLicense == null) {
                    continue;
                }

                $findWhmcsServer = WhmcsServer::where('id', $findLicense->whmcs_server_id)->first();
                if ($findWhmcsServer == null) {
                    continue;
                }

                $ip = request()->ip();
                $domain = '';

                if (isset($requestHeaders['x-mw-site-url'])) {
                    $parseUrl = parse_url($requestHeaders['x-mw-site-url']);
                    if (isset($parseUrl['host'])) {
                        $domain = $parseUrl['host'];
                    }
                }

                $consumeStatus = WhmcsLicenseValidatorHelper::licenseConsume($findWhmcsServer->url, $domain, $ip, $findLicense->license);

                $licenseLog = new LicenseLog();
                $licenseLog->license_id = $findLicense->id;
                $licenseLog->last_access = Carbon::now();
                $licenseLog->ip = $ip;
                $licenseLog->package_id = $request->get('id');
                // $licenseLog->mw_version = $request->get('id');
                $licenseLog->save();

            }
        }

        $targetVersion = $request->get('version', false);
        $findPackage = Package::where('id', $request->get('id'))->first();

        if ($findPackage !== null) {
            $json = json_decode($findPackage->package_json, true);
            $json = end($json);
            if (isset($json[$targetVersion])) {

                $version = $json[$targetVersion];
                $url = $version['dist']['url'];
                $url = str_replace('https://example.com/', config('app.url'), $url);

                return redirect($url);
            }
        }

        return false;
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

        return $this->getTeamPackages($request, $findTeam->id);
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

            return $this->getTeamPackages($request, $findTeam->id, ['package_id'=>$findPackageByName->id]);

        }
    }

    protected function getTeamPackages($request, $teamId, $filter = [])
    {
        ini_set('memory_limit', '512M');

        $findTeam = Team::where('id', $teamId)->with('whmcsServer')->first();
        if ($findTeam == null) {
            return [];
        }

        $teamPackages = TeamPackage::where('team_id', $findTeam->id)
            ->whereHas('package', function (Builder $query) use ($filter) {
                if (isset($filter['package_id'])) {
                    $query->where('id', $filter['package_id']);
                }
                $query->whereNotIn('clone_status', [Package::CLONE_STATUS_FAILED]);
            })
            ->where('is_visible', 1)
            ->orderBy('position','asc')
            ->with('package')
            ->get();

        if ($teamPackages == null) {
            return [];
        }

        $whmcsServer = [];
        if ($findTeam->whmcsServer != null) {
            $whmcsServer = $findTeam->whmcsServer->toArray();
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

        $validateLicense = [];
        if (isset($whmcsServer['id'])) {
            $validateLicense = $this->validateLicenses($request, $whmcsServer['id']);
        }

        $allPackages = [];

        $yml = [];
        $format = $request->get('format', false);

        if ($teamPackages->count() > 0) {
            foreach ($teamPackages as $teamPackage) {

                $package = $teamPackage->package;
                $packageJson = $package->package_json;
                $packageJson = str_replace('https://example.com/', config('app.url'), $packageJson);
                $packageContent = json_decode($packageJson, true);

                if (!empty($packageContent) && is_array($packageContent)) {
                    foreach ($packageContent as $packageName => $packageVersions) {

                        $allPackages[$packageName] = $this->_prepareVersions($packageVersions, [
                            'token_authenticated' => $logged,
                            'team_id' => $teamPackage->team_id,
                            'package_id' => $teamPackage->package_id,
                            'team_package_id' => $teamPackage->id,
                            'whmcs_primary_product_id' => $teamPackage->whmcs_primary_product_id,
                            'whmcs_product_ids' => $teamPackage->getWhmcsProductIds(),
                            'whmcs_server' => $whmcsServer,
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

        return ['packages'=>$allPackages,'time'=>time()];

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
        $extraMeta = [];
        if (isset($package['extra']['_meta'])) {
            if (isset($package['extra']['_meta']['readme'])) {
                $extraMeta['readme'] = $package['extra']['_meta']['readme'];
            }
            if (isset($package['extra']['_meta']['screenshot'])) {
                $extraMeta['screenshot'] = $package['extra']['_meta']['screenshot'];
            }
        }
        $package['extra']['_meta'] = $extraMeta;

        if (isset($package['extra']['preview_url'])) {
            if (isset($teamPackage['team_settings']['package_manager_templates_demo_domain'])) {

                $previewUrl = $package['extra']['preview_url'];
                $previewUrl = str_replace('templates.microweber.com', $teamPackage['team_settings']['package_manager_templates_demo_domain'], $previewUrl);

                $package['extra']['preview_url'] = $previewUrl;
            }
        }

        $package['notification-url'] = route('packages.download-notify');

        $whmcsUrl = '';
        if (isset($teamPackage['whmcs_server']['url'])) {
            $whmcsUrl = $teamPackage['whmcs_server']['url'];
        }
        $package['extra']['whmcs']['whmcs_url'] = $whmcsUrl;

        if (isset($teamPackage['is_paid']) && $teamPackage['is_paid'] == 1) {
            if (isset($teamPackage['whmcs_product_ids']) && !empty($teamPackage['whmcs_product_ids'])) {
                $licensed = false;

              /*  if (!$licensed and isset($teamPackage['token_authenticated']) && $teamPackage['token_authenticated'] === true) {
                    $licensed = true;
                }*/

                if (!$licensed) {
                    $package['dist'] = [
                        "type" => "license_key",
                        "url" => $whmcsUrl,
                        "reference" => "license_key",
                        "shasum" => "license_key"
                    ];
                }

              /*  if ($licensed && $userLicenseKeysValid) {
                    $package['dist']['url'] = URL::temporarySignedRoute(
                        'packages.download-private', now()->addMinutes(30), [
                            'license_ids' => base64_encode(json_encode($internalLicenseIds)),
                            'id' => $teamPackage['package_id'],
                            'version' => $package['version'],
                            'ip' => request()->ip()
                        ]
                    );
                }*/

            /*    if ($licensed) {
                    if (isset($teamPackage['team_package_id'])) {
                        if ($userLicenseKeysValid) {
                            $dataForNotification = [];
                            $dataForNotification['valid_license_keys'] = $userLicenseKeysValid;
                            $dataForNotification['package_name'] = $package['name'];
                            $dataForNotification['team_package_id'] = $teamPackage['team_package_id'];

                            $package['notification-url'] = route('packages.download-notify-private')
                                . '?used_keys_data='
                                . urlencode(base64_encode(json_encode($dataForNotification)));
                        }
                    }
                }*/

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

    public function validateLicenses($request, $whmcsServerId)
    {
        $findWhmcsServer = WhmcsServer::where('id', $whmcsServerId)->first();
        if ($findWhmcsServer == null) {
            return [];
        }

        $requestHeaders = collect($request->header())->transform(function ($item) {
            return $item[0];
        });

        $ip = request()->ip();
        $domain = '';

        if (isset($requestHeaders['x-mw-site-url'])) {
            $parseUrl = parse_url($requestHeaders['x-mw-site-url']);
            if (isset($parseUrl['host'])) {
                $domain = $parseUrl['host'];
            }
        }

        $internalLicenseIds = [];
        $licenseKeysValid = [];
        $licenseKeysInvalid = [];

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $_SERVER["HTTP_AUTHORIZATION"] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (isset($_SERVER["HTTP_AUTHORIZATION"]) && (strpos(strtolower($_SERVER["HTTP_AUTHORIZATION"]), 'basic') !== false)) {

            ///  file_put_contents(base_path().'/lic.txt', print_r((substr($_SERVER["HTTP_AUTHORIZATION"], 6)),1));

            $userLicenseKeys = base64_decode(substr($_SERVER["HTTP_AUTHORIZATION"], 6));

            if (is_string($userLicenseKeys) and (strpos(strtolower($userLicenseKeys), 'license:') !== false)) {
                $userLicenseKeys = substr($userLicenseKeys, 8);
                if(StringHelper::isBase64Encoded($userLicenseKeys)){
                    $userLicenseKeys = base64_decode($userLicenseKeys);
                }

            }
            if (is_string($userLicenseKeys) and (strpos(strtolower($userLicenseKeys), 'license:') !== false)) {
                $userLicenseKeys = substr($userLicenseKeys, 8);
                if(StringHelper::isBase64Encoded($userLicenseKeys)){
                    $userLicenseKeys = base64_decode($userLicenseKeys);
                }
            }

            if(StringHelper::isJSON($userLicenseKeys)) {
                $userLicenseKeysJson = json_decode($userLicenseKeys, true);
            } else {
                $userLicenseKeysJson = [];
                $userLicenseKeysJson['none'] = ['rel_type' =>'none','local_key'=>$userLicenseKeys];
            }
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
                        if(isset($userLicenseKey['rel_type'])){
                            $userLicenseKeysMap[$userLicenseKey['rel_type']] = $userLicenseKey['local_key'];
                        }
                    }
                }

                if (!empty($userLicenseKeysMap)) {
                    foreach ($userLicenseKeysMap as $k=>$userLicenseKey) {
                       /* $licenseKeyStatus = WhmcsLicenseValidatorHelper::getLicenseKeyStatus($findWhmcsServer->url, $userLicenseKey);
                        dump($licenseKeyStatus);*/
                        $consumeLicense = WhmcsLicenseValidatorHelper::licenseConsume($findWhmcsServer->url,$domain,$ip, $userLicenseKey);
                        if (isset($consumeLicense['status']) && $consumeLicense['status']=='Active') {
                            $licenseKeysValid[] = [
                                'status'=> 'active',
                                'license'=> $userLicenseKey,
                            ];
                        } else {
                            $messaage = '';
                            if (isset($consumeLicense['message'])) {
                                $messaage = $consumeLicense['message'];
                            }
                            $licenseKeysInvalid[] = [
                                'message'=> $messaage,
                                'status'=> mb_strtolower($consumeLicense['status']),
                                'license'=> $userLicenseKey,
                            ];
                        }
                    }
                }
            }
        }

        return [
            'license_ids'=>$internalLicenseIds,
            'license_invalid'=>$licenseKeysInvalid,
            'license_valid'=>$licenseKeysValid,
        ];
    }

}
