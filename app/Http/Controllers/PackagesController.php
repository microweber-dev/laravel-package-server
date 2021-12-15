<?php

namespace App\Http\Controllers;

use App\Helpers;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    private $whmcs_url = false;
    private $packageManagerEnv = false;
    private $repositories = [];

    public function __construct() {

        $this->packageManagerEnv = Helpers::getValuesFromEnvConfig('package-manager');

        $satisFile = Helpers::getEnvConfigDir() . 'satis.json';
        $satis = file_get_contents($satisFile);
        $satis = json_decode($satis, true);

        if ($satis) {
            $this->whmcs_url = $satis['whmcs_url'];
            if (isset($satis['repositories'])) {
                foreach ($satis['repositories'] as $repository) {
                    $repositoryUrl = $this->_clearRepositoryUrl($repository['url']);
                    $this->repositories[$repositoryUrl] = $repository;
                }
            }
        }

    }

    public function index() {
/*
        file_put_contents(date('Y-m-d-H-i-s').'.txt', json_encode($_SERVER, JSON_PRETTY_PRINT));*/

        $packages = $this->_getCompiledPackageJson();

        return [
            'packages'=>$packages
        ];
    }

    public function test()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, route('packages-json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic bGljZW5zZTpSVzUwWlhKd2NtbHpaV1EwWlRKbU16SmxOekk9',
        ));

        $contents = curl_exec($ch);
        $packages = json_decode($contents, true);
        if (!empty($packages)) {
            $packages = $packages['packages']['mw-internal/white_label']['0.2']['dist'];
            dump($packages);
        } else {
            echo $contents;
        }
    }


    private function _getCompiledPackageJson()
    {
        $publicEnvFolder = dirname(dirname(dirname(dirname(__DIR__)))).'/public_html/domains/'. Helpers::getEnvName();
        $packagesFile = $publicEnvFolder . '/original-packages.json';

        $packages = [];
        $compiledPackages = $this->_jsonDecodeFile($packagesFile);
        if ($compiledPackages) {
            foreach ($compiledPackages as $compiledPackage) {
                if (is_array($compiledPackage)) {
                    foreach ($compiledPackage as $package=>$packageSha) {
                        $packageFile = $publicEnvFolder .'/'. $package;
                        $getPackages = $this->_jsonDecodeFile($packageFile);
                        if ($getPackages['packages']) {
                            foreach ($getPackages['packages'] as $packageName=>$packageVersions) {
                                $packages[$packageName] = $this->_prepareVersions($packageVersions);
                            }
                        }
                    }
                }
            }
        }

        return $packages;
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
            if (!empty($this->packageManagerEnv['package_manager_templates_demo_domain'])) {

                $previewUrl = $package['extra']['preview_url'];
                $previewUrl = str_replace('templates.microweber.com', $this->packageManagerEnv['package_manager_templates_demo_domain'], $previewUrl);

                $package['extra']['preview_url'] = $previewUrl;
            }
        }

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

    private function _jsonDecodeFile($file) {
        $json = file_get_contents($file);
        $json = json_decode($json, TRUE);
        return $json;
    }
}
