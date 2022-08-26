<?php

namespace App\Helpers;

class StringHelper
{
   public static  function isBase64Encoded($string): bool
    {
        if (!is_string($string)) {
            // if check value is not string.
            // base64_decode require this argument to be string, if not then just return `false`.
            // don't use type hint because `false` value will be converted to empty string.
            return false;
        }

        $decoded = base64_decode($string, true);
        if (false === $decoded) {
            return false;
        }

        if (json_encode([$decoded]) === false) {
            return false;
        }

        return true;
    }


    public static   function isJSON($string) {

        return (is_null(json_decode($string))) ? FALSE : TRUE;
    }
}
