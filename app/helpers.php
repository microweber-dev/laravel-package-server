<?php

if (!function_exists('rmdir_recursive')) {
    function rmdir_recursive($directory, $empty = true)
    {
        // if the path has a slash at the end we remove it here
        if (substr($directory, -1) == DIRECTORY_SEPARATOR) {
            $directory = substr($directory, 0, -1);
        }

        // if the path is not valid or is not a directory ...
        if (!is_dir($directory)) {
            // ... we return false and exit the function
            return false;

            // ... if the path is not readable
        } elseif (!is_readable($directory)) {
            // ... we return false and exit the function
            return false;

            // ... else if the path is readable
        } else {
            // we open the directory
            $handle = opendir($directory);

            // and scan through the items inside
            while (false !== ($item = readdir($handle))) {
                // if the filepointer is not the current directory
                // or the parent directory
                if ($item != '.' && $item != '..') {
                    // we build the new path to delete
                    $path = $directory.DIRECTORY_SEPARATOR.$item;

                    // if the new path is a directory
                    if (is_dir($path)) {

                        // we call this function with the new path
                        rmdir_recursive($path, $empty);
                        // if the new path is a file
                    } else {
                        try {
                            @unlink($path);
                        } catch (Exception $e) {
                        }
                    }
                }
            }

            // close the directory
            closedir($handle);

            // if the option to empty is not set to true
            if ($empty == false) {
                @rmdir($directory);
            }

            // return success
            return true;
        }
    }
}



if (!function_exists('normalize_path')) {
    /**
     * Converts a path in the appropriate format for win or linux.
     *
     * @param string $path
     *                         The directory path.
     * @param bool $slash_it
     *                         If true, ads a slash at the end, false by default
     *
     * @return string The formatted string
     */
    function normalize_path($path, $slash_it = true)
    {
        $path_original = $path;
        $s = DIRECTORY_SEPARATOR;
        $path = preg_replace('/[\/\\\]/', $s, $path);
        $path = str_replace($s . $s, $s, $path);
        if (strval($path) == '') {
            $path = $path_original;
        }
        if ($slash_it == false) {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        } else {
            $path .= DIRECTORY_SEPARATOR;
            $path = rtrim($path, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        }
        if (strval(trim($path)) == '' or strval(trim($path)) == '/') {
            $path = $path_original;
        }
        if ($slash_it == false) {
        } else {
            $path = $path . DIRECTORY_SEPARATOR;
            $path = reduce_double_slashes($path);
        }

        return $path;
    }
}




if (!function_exists('reduce_double_slashes')) {
    /**
     * Removes double slashes from sting.
     *
     * @param $str
     *
     * @return string
     */
    function reduce_double_slashes($str)
    {
        return preg_replace('#([^:])//+#', '\\1/', $str);
    }
}

function mkdir_recursive($pathname)
{
    if ($pathname == '') {
        return false;
    }
    is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname));

    return is_dir($pathname) || @mkdir($pathname);
}
