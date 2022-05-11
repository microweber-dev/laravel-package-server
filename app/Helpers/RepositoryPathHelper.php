<?php

namespace App\Helpers;

class RepositoryPathHelper
{

    public static function getRepositoriesClonePath($id = false)
    {
        if (!$id) {
            throw new \Exception('Please set package id');
        }

        $path =  storage_path() . '/repositories/' . $id . '/';
        if (!is_dir($path)) {
            mkdir($path,0755,true);
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
            mkdir($path,0755,true);
        }
        return $path;
    }

    public static function getRepositoryProviderByUrl($repositoryUrl) {

        $parse = parse_url($repositoryUrl);
        if (isset($parse['host'])) {
            $host = $parse['host'];
            $host = str_replace('.com', '', $host);
            $host = str_replace('.net', '', $host);
            $host = str_replace('.org', '', $host);
            return $host;
        }

        return 'unknown';
    }
}
