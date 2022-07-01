<?php

namespace App\Helpers;

use Github\Client;
use Github\HttpClient\Message\ResponseMediator;
use GrahamCampbell\GitHub\Facades\GitHub;

class GithubHelper
{
    public static function getAvailableWorkers()
    {
        $available = 0;
        $client = GitHub::connection('main');
        $response = $client->getHttpClient()->get('/orgs/microweber-api/actions/runners');
        $check = ResponseMediator::getContent($response);


        dd($check);
        if (isset($check['runners'])) {
            foreach ($check['runners'] as $runner) {
                if ($runner['status']=='online' && $runner['busy'] == false) {
                    $available++;
                }
            }
        }

        return $available;
    }

}
