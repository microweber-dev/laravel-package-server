<?php

namespace App\Helpers;

use GrahamCampbell\GitHub\Facades\GitHub;

class GithubHelper
{

    public static function getAvailableWorkers()
    {

       $a = GitHub::connection('main')
           ->repositories()
           ->selfHostedRunners()
           ->all('microweber-api', 'package-manager-worker');

       dd($a);

    }

}
