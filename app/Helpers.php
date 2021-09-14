<?php
namespace App;

class Helpers
{
    public static function getEnvName()
    {
        return \Request::server("SERVER_NAME");
    }
}
