<?php

namespace App\Helpers;

class Base
{
    /**
     * return DOS OR UNIX
     */
    public static function familyOs() {
        return (stripos(PHP_OS, "WIN") === 0)? "DOS" : "UNIX";
    }
}
