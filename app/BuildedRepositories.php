<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:59 PM
 */

namespace App;

use Symfony\Component\Finder\Finder;

class BuildedRepositories
{
    public function getBuildInfoByUrl($url)
    {
        $buildInfo = [
            'version'=>'--',
            'type'=>'--',
            'name'=>'--',
            'description'=>'--',
        ];
        $repositoryLastVersions = [];
        foreach ($this->get() as $repositoryName => $repositoryVersions) {
            $repositoryLastVersions[] = end($repositoryVersions);
        }

        foreach($repositoryLastVersions as $repository) {
            if (isset($repository['source']['url']) && $repository['source']['url'] == $url) {
                $buildInfo = $repository;
                break;
            }
        }

        return $buildInfo;
    }

    public function get()
    {
        $builded = [];

        $buildedPackagesPath = base_path() . '/public/include/';

        $finder = new Finder();
        $finder->files()->in($buildedPackagesPath)->name(['*.json']);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $builded = json_decode($file->getContents(), true);
                if (isset($builded['packages'])) {
                    $builded = $builded['packages'];
                }
                break;
            }
        }

        return $builded;
    }
}
