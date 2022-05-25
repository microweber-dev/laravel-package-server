<?php

namespace App\Helpers;


class WhmcsLicenseValidatorHelper
{

    public static $key_status_check_cache = [];

    public static function validateLicenseKey($whmcsUrl, $key)
    {

        if (isset(self::$key_status_check_cache[$key])) {
            return self::$key_status_check_cache[$key];
        }
        $checkWhmcs = self::validateLicenseMakeRequest($whmcsUrl, $key);
        if (isset($checkWhmcs['status']) && $checkWhmcs['status'] == 'success') {
            self::$key_status_check_cache[$key] = true;
            return true;
        }
        self::$key_status_check_cache[$key] = false;
        return false;

    }

    public static $key_status_check_cache_get = [];

    public static function getLicenseKeyStatus($whmcsUrl, $key)
    {
        if (isset(self::$key_status_check_cache_get[$key])) {
            return self::$key_status_check_cache_get[$key];
        }

        $checkWhmcs = self::validateLicenseMakeRequest($whmcsUrl, $key);
        if (isset($checkWhmcs['status'])) {
            self::$key_status_check_cache_get[$key] = $checkWhmcs;
            return $checkWhmcs;
        }
        self::$key_status_check_cache_get[$key] = false;
        return false;
    }


    public static function validateLicenseMakeRequest($whmcsUrl, $key)
    {
        $curl = curl_init();


        $checkWhmcsUrl = ($whmcsUrl . '/index.php?m=microweber_addon&function=validate_license&license_key=' . $key);

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

            if (isset($getResponse['status'])) {
                return $getResponse;
            }
            return [];
        }
    }
}
