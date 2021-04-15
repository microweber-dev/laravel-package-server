<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:59 PM
 */

namespace App;


use Symfony\Component\Finder\Finder;

class SatisManager
{
    public $name;
    public $homepage;
    public $whmcs_url;
    public $satis_file;
    public $builded_repositories;

    public $repositories = [];

    public function __construct()
    {
        $this->builded_repositories = $this->getBuildedRepositories();
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setHomepage($url) {
        $this->homepage = $url;
    }

    public function setWhmcsUrl($url) {
        $this->whmcs_url = $url;
    }

    public function load($file) {

        $satis = file_get_contents($file);
        if (!$satis) {
            throw new \Exception('Can\'t load satis file');
        }

        $satis = json_decode($satis, true);
        if (!$satis) {
            throw new \Exception('Can\'t decode satis file');
        }

        $this->satis_file = $file;

        foreach ($satis as $key=>$value) {
           $this->$key = $value;
        }

        if (isset($this->repositories)) {
            foreach($this->repositories as &$repository) {
                $repository = $this->getRepositoryByUrl($repository['url']);
            }
        }
    }

    public function saveRepository($data)
    {
        $this->deleteRepositoryByUrl($data['url']);

        $this->repositories[] = [
            'type'=>$data['type'],
             'url'=>$data['url'],
             'whmcs_product_ids'=>$data['whmcs_product_ids'],
       ];

    }

    public function getRepositoryByUrl($url) {

        foreach ($this->repositories as $repository) {
            if ($repository['url'] == $url) {

                $repository['build_info'] = false;

                if (isset($this->builded_repositories)) {
                    foreach ($this->builded_repositories as $repositoryName => $repositoryVersions) {
                        if (strpos($url, $repositoryName) !== false) {
                            $lastVersion = end($repositoryVersions);
                            $repository['build_info'] = $lastVersion;
                            break;
                        }
                    }
                }

                return $repository;
            }
        }

        return false;
    }

    public function deleteRepositoryByUrl($url)
    {
        foreach ($this->repositories as $repositoryKey=>$repository) {
            if ($repository['url'] == $url) {
                unset($this->repositories[$repositoryKey]);
            }
        }
    }

    public function getRepositories()
    {
        return $this->repositories;
    }

    public function save()
    {
        $save = (array) $this;
        unset($save['satis_file']);

        $encoded = json_encode($save, JSON_PRETTY_PRINT);

        file_put_contents($this->satis_file, $encoded);
    }

    public function getBuildedRepositories()
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
