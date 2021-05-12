<?php

namespace App\Http\Controllers;

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
        $envPath = app()->environmentFilePath();
        try {
            $envEditor = \DotenvEditor::load($envPath);
            $whmcsApiUrl = $envEditor->getValue('WHMCS_API_URL');
            $whmcsAuthType = $envEditor->getValue('WHMCS_AUTH_TYPE');
            $whmcsApiIdentifier = $envEditor->getValue('WHMCS_API_IDENTIFIER');
            $whmcsApiSecret = $envEditor->getValue('WHMCS_API_SECRET');
            $whmcsUsername = $envEditor->getValue('WHMCS_USERNAME');
            $whmcsPassword = $envEditor->getValue('WHMCS_PASSWORD');
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

        $envPath = app()->environmentFilePath();
        $envEditor = \DotenvEditor::load($envPath);

        $envEditor->setKey('WHMCS_URL', $request->post('whmcs_url'))->save();
        $envEditor->setKey('WHMCS_API_URL', $request->post('whmcs_url') . '/includes')->save();
        $envEditor->setKey('WHMCS_AUTH_TYPE', $request->post('whmcs_auth_type'))->save();

        $envEditor->setKey('WHMCS_API_IDENTIFIER', $request->post('whmcs_api_identifier'))->save();
        $envEditor->setKey('WHMCS_API_SECRET', $request->post('whmcs_api_secret'))->save();

        $envEditor->setKey('WHMCS_USERNAME', $request->post('whmcs_username'))->save();
        $envEditor->setKey('WHMCS_PASSWORD', $request->post('whmcs_password'))->save();

        return redirect(route('configure-whmcs'));
    }
}
