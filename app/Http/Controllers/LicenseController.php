<?php

namespace App\Http\Controllers;

use App\Helpers\WhmcsLicenseValidatorHelper;
use App\Models\Team;
use App\Models\WhmcsServer;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function checkFromDomain(Request $request)
    {
        $host = $request->getHost();
        $findTeam = Team::where('domain', $host)->first();

        if ($findTeam == null) {
            return [];
        }

        $ip = $request->ip();
        $license = $request->get('key', false);
        $domain = $this->__getDomainFromHeaders($request);

        return $this->checkLicense($findTeam->id, $license, $ip, $domain);
    }

    public function check($slug, Request $request)
    {
        if (!$slug) {
            return [];
        }

        $findTeam = Team::where('slug', $slug)->first();
        if ($findTeam == null) {
            return ['valid' => false];
        }

        $ip = $request->ip();
        $license = $request->get('key', false);
        $domain = $this->__getDomainFromHeaders($request);

        return $this->checkLicense($findTeam->id, $license, $ip, $domain);
    }

    public function checkLicense($teamId, $license, $ip, $domain = '')
    {
        $findTeam = Team::where('id', $teamId)->first();
        if ($findTeam == null) {
            return ['valid' => false];
        }

        if ($findTeam->whmcsServer == null) {
            return ['valid' => false];
        }


        // New license server
        try {
            $checkLicense = @file_get_contents('https://microweber.com/license-server/validate-legacy?&local_key=' . $license . '&rel_type=default');
            $checkLicense = json_decode($checkLicense, true);

            if (isset($checkLicense['default'])) {

                if (isset($checkLicense['default']['status']) && strtolower($checkLicense['default']['status'] )== 'active') {
                    $checkLicense['default']['local_key'] = $license;
                    $checkLicense['default']['status'] = 'Active';
                    $checkLicense['default']['productid'] = '0';
                    return ['valid' => true, 'details' => $checkLicense['default']];
                }
            }
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }

        // Must be deprecated soon
        $consumeLicense = WhmcsLicenseValidatorHelper::licenseConsume($findTeam->whmcsServer->url, $domain, $ip, $license);
        if (isset($consumeLicense['localkey'])) {
            unset($consumeLicense['localkey']);
        }

        if (isset($consumeLicense['status']) && $consumeLicense['status'] == 'Active') {
            return ['valid' => true, 'details' => $consumeLicense];
        }

        return ['valid' => false, 'details' => $consumeLicense];

    }

    private function __getDomainFromHeaders($request)
    {
        $domain = '';
        $requestHeaders = collect($request->header())->transform(function ($item) {
            return $item[0];
        });
        if (isset($requestHeaders['x-mw-site-url'])) {
            $parseUrl = parse_url($requestHeaders['x-mw-site-url']);
            if (isset($parseUrl['host'])) {
                $domain = $parseUrl['host'];
            }
        }

        return $domain;
    }
}
