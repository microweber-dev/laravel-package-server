<?php

namespace App\Helpers;

use Github\Client;
use Github\HttpClient\Message\ResponseMediator;
use GrahamCampbell\GitHub\Facades\GitHub;

class GithubHelper
{
    public static $runnersApiUrl='/orgs/microweber-api/actions/runners';

    public static function getAvailableWorkers()
    {
        $available = 0;
        $client = GitHub::connection('main');
        $response = $client->getHttpClient()->get(self::$runnersApiUrl);
        $check = ResponseMediator::getContent($response);

        if (isset($check['runners'])) {
            foreach ($check['runners'] as $runner) {
                if ($runner['status']=='online' && $runner['busy'] == false) {
                    $available++;
                }
            }
        }

        return $available;
    }

    public static function getBusyWorkers()
    {
        $busy = 0;
        $client = GitHub::connection('main');
        $response = $client->getHttpClient()->get(self::$runnersApiUrl);
        $check = ResponseMediator::getContent($response);

        if (isset($check['runners'])) {
            foreach ($check['runners'] as $runner) {
                if ($runner['status']=='online' && $runner['busy'] == true) {
                    $busy++;
                }
            }
        }

        return $busy;
    }

}
