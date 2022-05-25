<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageDownloadStats;
use App\Models\Team;
use App\Models\TeamPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PackageInstallNotifyController extends Controller
{


    public function downloadNotifyPrivate(Request $request)
    {
        $used_keys_data = [];
        $package_name = false;
        $data = $request->all();
        if(isset($data['used_keys_data'])){
            $used_keys_data = @json_decode(base64_decode(urldecode($data['used_keys_data'])),true);
        }
        if(isset($data['package_name'])){
            $package_name = urldecode($data['package_name']);
        }


        return $this->downloadNotify($request);
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


}
