<?php

namespace App;

class RepositoryPathHelper
{

    public static function getRepositoriesClonePath($id = false)
    {
        if (!$id) {
            throw new \Exception('Please set package id');
        }

        $path =  storage_path() . '/repositories/' . $id . '/';
        if (!is_dir($path)) {
            mkdir($path,755,true);
        }
        return $path;
    }

    public static function getRepositoriesSatisPath($id = false)
    {
        if (!$id) {
            throw new \Exception('Please set package id');
        }

        $path =  storage_path() . '/repositories-satis/' . $id . '/';
        if (!is_dir($path)) {
            mkdir($path,755,true);
        }
        return $path;
    }

}
