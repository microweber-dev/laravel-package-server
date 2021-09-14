<?php

namespace App\Http\Controllers;

use App\Helpers;
use App\Jobs\PackageManagerBuildJob;
use App\SatisManager;
use Illuminate\Http\Request;

class WhmcsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            $packageManager = Helpers::getValuesFromEnvConfig('whmcs');

            $whmcsApiUrl = $packageManager['whmcs_api_url'];
            $whmcsAuthType = $packageManager['whmcs_auth_type'];
            $whmcsApiIdentifier = $packageManager['whmcs_api_identifier'];
            $whmcsApiSecret = $packageManager['whmcs_api_secret'];
            $whmcsUsername = $packageManager['whmcs_username'];
            $whmcsPassword = $packageManager['whmcs_password'];

        } catch (\Exception $e) {
            $whmcsApiUrl = '';
            $whmcsAuthType = '';
            $whmcsApiIdentifier = '';
            $whmcsApiSecret = '';
            $whmcsUsername = '';
            $whmcsPassword = '';
        }

       $whmcsUrl = '';
        $parsed = parse_url($whmcsApiUrl);
        if (isset($parsed['scheme'])) {
            $whmcsUrl .= $parsed['scheme'].'://';
        }
        if (isset($parsed['host'])) {
            $whmcsUrl .= $parsed['host'];
        }

        return view('whmcs.index',[
            'whmcs_url' => $whmcsUrl,
            'whmcs_auth_type' => $whmcsAuthType,
            'whmcs_api_identifier' => $whmcsApiIdentifier,
            'whmcs_api_secret' => $whmcsApiSecret,
            'whmcs_username' => $whmcsUsername,
            'whmcs_password' => $whmcsPassword,
        ]);
    }


    public function getConnectionStatus()
    {
        try {
            $checkConnection = \Whmcs::GetProducts();
        } catch (\Exception $e) {
            return ['error'=> $e->getMessage()];
        }

        if (empty($checkConnection)) {
            return ['error'=>'Something went wrong. Can\'t connect to the WHMCS.'];
        }

        if (isset($checkConnection['result']) && $checkConnection['result'] == 'error') {
            return ['error'=>$checkConnection['message']];
        }

        return ['success'=>'Connection with WHMCS is successfully.'];
    }

    public function save(Request $request) {

        PackageManagerBuildJob::dispatch();

        $values = [];
        $values['whmcs_url'] = $request->post('whmcs_url');
        $values['whmcs_api_url'] = $request->post('whmcs_url') . '/includes';
        $values['whmcs_auth_type'] = $request->post('whmcs_auth_type');

        $values['whmcs_api_identifier'] = $request->post('whmcs_api_identifier');
        $values['whmcs_api_secret'] = $request->post('whmcs_api_secret');

        $values['whmcs_username'] = $request->post('whmcs_username');
        $values['whmcs_password'] = $request->post('whmcs_password');

        Helpers::setValuesToEnvConfig('whmcs', $values);

        return redirect(route('configure-whmcs'));
    }
}
