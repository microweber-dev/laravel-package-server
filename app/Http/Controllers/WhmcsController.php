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

            $whmcsApiUrl = $packageManager['apiurl'];
            $whmcsAuthType = $packageManager['auth_type'];
            $whmcsApiIdentifier = $packageManager['api']['identifier'];
            $whmcsApiSecret = $packageManager['api']['secret'];
            $whmcsUsername = $packageManager['password']['username'];
            $whmcsPassword = $packageManager['password']['password'];

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
        $values['url'] = $request->post('whmcs_url');
        $values['apiurl'] = $request->post('whmcs_url') . '/includes';
        $values['auth_type'] = $request->post('whmcs_auth_type');

        $values['api']['identifier'] = $request->post('whmcs_api_identifier');
        $values['api']['secret'] = $request->post('whmcs_api_secret');

        $values['password']['username'] = $request->post('whmcs_username');
        $values['password']['password'] = $request->post('whmcs_password');

        Helpers::setValuesToEnvConfig('whmcs', $values);

        return redirect(route('configure-whmcs'));
    }
}
