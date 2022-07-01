<?php

namespace App\Helpers;

use GrahamCampbell\GitHub\Facades\GitHub;

class GithubHelper
{

    public static function getAvailableWorkers()
    {

       $a = GitHub::me()->organizations();

       dd($a);

    }

}
