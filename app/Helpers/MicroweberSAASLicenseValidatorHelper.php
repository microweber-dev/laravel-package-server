<?php

namespace App\Helpers;


class MicroweberSAASLicenseValidatorHelper
{
    public static function validateLicenseMakeRequest($key)
    {
        $curl = curl_init();

        $checkWhmcsUrl = 'https://microweber.com/license-server/validate-legacy?&local_key='.$key.'&rel_type=whitelabel';

        $opts = [
            CURLOPT_URL => $checkWhmcsUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",

        ];

        curl_setopt_array($curl, $opts);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            // return ["error" => "cURL Error #:" . $err];
            return [];
        } else {
            $getResponse = json_decode($response, true);

            return $getResponse;
        }
    }

    public static function getLicenseStatus($licenseKey)
    {
        $license = self::validateLicenseMakeRequest($licenseKey);

        if (isset($license['whitelabel'])) {
            return $license['whitelabel'];
        }

        return [];
    }
}
