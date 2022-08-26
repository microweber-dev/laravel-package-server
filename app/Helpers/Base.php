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


    public static function humanFilesize($bytes, $dec = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

}
