<?php

namespace App\Http\Controllers;

use App\Helpers\WhmcsLicenseValidatorHelper;
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
        $valid_keys = [];
        $package_name = false;
        $whmcs_url = false;
        $team_package_id = false;
        $data = $request->all();

        /*$data['server'] = $_SERVER;
        $data['request'] = $_REQUEST;
        file_put_contents(storage_path().'/manqk'.time().'.txt', json_encode($data, JSON_PRETTY_PRINT));
        */
        
        if (isset($data['used_keys_data'])) {
            $used_keys_data = @json_decode(base64_decode(urldecode($data['used_keys_data'])), true);
        }
        if (isset($used_keys_data['package_name'])) {
            $package_name = $used_keys_data['package_name'];
        }
        if (isset($used_keys_data['team_package_id'])) {
            $team_package_id = $used_keys_data['team_package_id'];
        }
        if (isset($used_keys_data['valid_license_keys'])) {
            $valid_keys = $used_keys_data['valid_license_keys'];
        }

        if ($valid_keys and $team_package_id and $package_name) {
            $checkTeamPackage = TeamPackage::where('id', $team_package_id)->first();
            $checkTeam = Team::where('id', $checkTeamPackage->team_id)->first();

            if ($checkTeam) {
                $teamSettings = $checkTeam->settings()->get();

                if ($teamSettings) {
                    if (isset($teamSettings['whmcs_url']) and $teamSettings['whmcs_url'] and !filter_var($teamSettings['whmcs_url'], FILTER_VALIDATE_URL) === false) {
                        $whmcs_url = $teamSettings['whmcs_url'];
                    }

                    if ($whmcs_url) {
                        foreach ($valid_keys as $valid_key_prefix => $valid_key) {
                            // @todo finish this
                           // $status = WhmcsLicenseValidatorHelper::getLicenseKeyStatus($whmcs_url,$valid_key);
                            //dump($status);
                        }
                    }

                }
            }

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
    return ['time'=>now()];
    }


}
