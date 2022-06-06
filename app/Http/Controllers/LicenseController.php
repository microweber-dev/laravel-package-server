<?php

namespace App\Http\Controllers;

use App\Helpers\WhmcsLicenseValidatorHelper;
use App\Models\Team;
use App\Models\WhmcsServer;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function check($slug, Request $request)
    {
        if (!$slug) {
            return [];
        }

        $findTeam = Team::where('slug', $slug)->first();
        if ($findTeam == null) {
            return ['valid'=>false];
        }

        if ($findTeam->whmcsServer == null) {
            return ['valid'=>false];
        }

        $license = $request->get('license', false);
        $ip = $request->ip();
        $domain = '';

        if (isset($requestHeaders['x-mw-site-url'])) {
            $parseUrl = parse_url($requestHeaders['x-mw-site-url']);
            if (isset($parseUrl['host'])) {
                $domain = $parseUrl['host'];
            }
        }

        $consumeLicense = WhmcsLicenseValidatorHelper::licenseConsume($findTeam->whmcsServer->url, $domain, $ip, $license);
        if (isset($consumeLicense['status']) && $consumeLicense['status'] == 'Active') {
            return ['valid'=>true,'details'=>$consumeLicense];
        }

        return ['valid'=>false,'details'=>$consumeLicense];

    }
}
