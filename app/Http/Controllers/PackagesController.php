<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PackagesController extends Controller
{
    public function index() {

        $packages = $this->_getCompiledPackageJson();

        return [
            'packages'=>$packages
        ];
    }


    private function _getCompiledPackageJson()
    {
        $packages = [];
        $compiledPackages = $this->_jsonDecodeFile( 'original-packages.json');
        if ($compiledPackages) {
            foreach ($compiledPackages as $compiledPackage) {
                if (is_array($compiledPackage)) {
                    foreach ($compiledPackage as $package=>$packageSha) {
                        $getPackages = $this->_jsonDecodeFile($package);
                        if ($getPackages['packages']) {
                            $packages = array_merge($packages, $getPackages['packages']);
                        }
                    }
                }
            }
        }

        return $packages;
    }

    private function _jsonDecodeFile($file) {
        $json = file_get_contents($file);
        $json = json_decode($json, TRUE);
        return $json;
    }
}
