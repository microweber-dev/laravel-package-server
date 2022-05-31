<?php

namespace App\Helpers;


class WhmcsLicenseValidatorHelper
{

    private static $key_status_check_cache = [];

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

    private static $key_status_check_cache_get = [];

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

    /**
     * @param $whmcsurl Whmcs Url
     * @param $domain Cosumer Domain
     * @param $usersip Cosumer User Ip
     * @param $licensekey Cosumer License Key
     * @param $localkey Cosumer Local Key
     * @return array|mixed|string[]
     */
    public static function licenseConsume($whmcsurl,$domain,$usersip, $licensekey, $localkey = false)
    {
        $whmcsurl = rtrim($whmcsurl, '/') . '/';
        // Must match what is specified in the MD5 Hash Verification field
        // of the licensing product that will be used with this check.
        $licensing_secret_key = '';
        // The number of days to wait between performing remote license checks
        $localkeydays = 15;
        // The number of days to allow failover for after local key expiry
        $allowcheckfaildays = 5;
        // -----------------------------------
        //  -- Do not edit below this line --
        // -----------------------------------
        $check_token = time() . md5(mt_rand(100000000, mt_getrandmax()) . $licensekey);
        $checkdate = date("Ymd");
        $dirpath = dirname(__FILE__);
        $verifyfilepath = 'modules/servers/licensing/verify.php';
        $localkeyvalid = false;
        $originalcheckdate = false;
        if ($localkey) {
            $localkey = str_replace("\n", '', $localkey);
            # Remove the line breaks
            $localdata = substr($localkey, 0, strlen($localkey) - 32);
            # Extract License Data
            $md5hash = substr($localkey, strlen($localkey) - 32);
            # Extract MD5 Hash
            if ($md5hash == md5($localdata . $licensing_secret_key)) {
                $localdata = strrev($localdata);
                # Reverse the string
                $md5hash = substr($localdata, 0, 32);
                # Extract MD5 Hash
                $localdata = substr($localdata, 32);
                # Extract License Data
                $localdata = base64_decode($localdata);
                $localkeyresults = json_decode($localdata, true);
                $originalcheckdate = $localkeyresults['checkdate'];
                if ($md5hash == md5($originalcheckdate . $licensing_secret_key)) {
                    $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - $localkeydays, date("Y")));
                    if ($originalcheckdate > $localexpiry) {
                        $localkeyvalid = true;
                        $results = $localkeyresults;
                        $validdomains = explode(',', $results['validdomain']);
                        if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                        $validips = explode(',', $results['validip']);
                        if (!in_array($usersip, $validips)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                        $validdirs = explode(',', $results['validdirectory']);
                        if (!in_array($dirpath, $validdirs)) {
                            $localkeyvalid = false;
                            $localkeyresults['status'] = "Invalid";
                            $results = array();
                        }
                    }
                }
            }
        }
        if (!$localkeyvalid) {
            $responseCode = 0;
            $postfields = array('licensekey' => $licensekey, 'domain' => $domain, 'ip' => $usersip, 'dir' => $dirpath);
            if ($check_token) {
                $postfields['check_token'] = $check_token;
            }
            $query_string = '';
            foreach ($postfields as $k => $v) {
                $query_string .= $k . '=' . urlencode($v) . '&';
            }
            if (function_exists('curl_exec')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $whmcsurl . $verifyfilepath);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $data = curl_exec($ch);
                $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }
            if ($responseCode != 200) {
                $localexpiry = date("Ymd", mktime(0, 0, 0, date("m"), date("d") - ($localkeydays + $allowcheckfaildays), date("Y")));
                if ($originalcheckdate > $localexpiry) {
                    $results = $localkeyresults;
                } else {
                    $results = array();
                    $results['status'] = "Invalid";
                    $results['description'] = "Remote Check Failed";
                    return $results;
                }
            } else {
                preg_match_all('/<(.*?)>([^<]+)<\\/\\1>/i', $data, $matches);
                $results = array();
                foreach ($matches[1] as $k => $v) {
                    $results[$v] = $matches[2][$k];
                }
            }
            if (!is_array($results)) {
                return ["error"=>"Invalid License Server Response"];
            }

            if (isset($results['status']) && $results['status'] == 'Invalid') {
                return $results;
            }

            if ($results['md5hash']) {
                if ($results['md5hash'] != md5($licensing_secret_key . $check_token)) {
                    $results['status'] = "Invalid";
                    $results['description'] = "MD5 Checksum Verification Failed";
                    return $results;
                }
            }
            if ($results['status'] == "Active") {
                $results['checkdate'] = $checkdate;
                $data_encoded = json_encode($results);
                $data_encoded = base64_encode($data_encoded);
                $data_encoded = md5($checkdate . $licensing_secret_key) . $data_encoded;
                $data_encoded = strrev($data_encoded);
                $data_encoded = $data_encoded . md5($data_encoded . $licensing_secret_key);
                $data_encoded = wordwrap($data_encoded, 80, "\n", true);
                $results['localkey'] = $data_encoded;
            }
            $results['remotecheck'] = true;
        }
        unset($postfields, $data, $matches, $whmcsurl, $licensing_secret_key, $checkdate, $usersip, $localkeydays, $allowcheckfaildays, $md5hash);
        return $results;
    }
}
