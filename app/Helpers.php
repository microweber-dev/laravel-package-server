<?php
namespace App;

class Helpers
{
    public static function getEnvName()
    {
        $environment = \Request::server("SERVER_NAME");

        if (php_sapi_name() == 'cli') {
            $environment = \App::environment();
        }

        return $environment;
    }
}
