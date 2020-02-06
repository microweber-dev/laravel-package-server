<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PackagesController extends Controller
{
    private $whmcs_url = false;
    private $repositories = [];

    public function __construct() {

        $satis = file_get_contents('../satis.json');
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

        $packages = $this->_getCompiledPackageJson();

        return [
            'packages'=>$packages
        ];
    }


    private function _getCompiledPackageJson()
    {
        $packages = [];
        $compiledPackages = $this->_jsonDecodeFile('original-packages.json');
        if ($compiledPackages) {
            foreach ($compiledPackages as $compiledPackage) {
                if (is_array($compiledPackage)) {
                    foreach ($compiledPackage as $package=>$packageSha) {
                        $getPackages = $this->_jsonDecodeFile($package);
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
        foreach ($versions as $version) {
            $prepareVersions[] = $this->_preparePackage($version);
        }

        return $prepareVersions;
    }

    private function _preparePackage($package) {

        $packageUrl = $this->_clearRepositoryUrl($package['source']['url']);
        if (isset($this->repositories[$packageUrl])) {
            $repositorySettings = $this->repositories[$packageUrl];
            if (isset($repositorySettings['whmcs_license_ids']) && !empty($repositorySettings['whmcs_license_ids'])) {
                $package['dist'] = [
                    "type" => "license_key",
                    "url" => "",
                    "reference" => "license_key",
                    "shasum" => "license_key"
                ];
            }
        }

        return $package;
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
