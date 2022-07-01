<?php

namespace App\Helpers;

use GrahamCampbell\GitHub\Facades\GitHub;

class GithubHelper
{

    public static function getAvailableWorkers()
    {

       $a = GitHub::connection('main')->me()->repositories();

       dd($a);

    }

}
