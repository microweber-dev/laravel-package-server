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
    public $repositories = [];

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setHomepage($url)
    {
        $this->homepage = $url;
    }

    public function setWhmcsUrl($url)
    {
        $this->whmcs_url = $url;
    }

    public function load($file)
    {
        if (!is_file($file)) {
            file_put_contents($file, json_encode(['name'=>'your packages']));
        }

        $satis = file_get_contents($file);
        if (!$satis) {
            throw new \Exception('Can\'t load satis file');
        }

        $this->satis_file = $file;

        $satis = json_decode($satis, true);
        if (!empty($satis)) {
            foreach ($satis as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    public function saveRepository($data)
    {
        $this->deleteRepositoryByUrl($data['url']);

        $this->repositories[] = [
            'type' => $data['type'],
            'url' => $data['url'],
            'whmcs_product_ids' => $data['whmcs_product_ids'],
        ];
    }

    public function getRepositoryByUrl($url)
    {
        foreach ($this->repositories as $repository) {
            if ($repository['url'] == $url) {
                return $repository;
            }
        }

        return false;
    }

    public function deleteRepositoryByUrl($url)
    {
        foreach ($this->repositories as $repositoryKey => $repository) {
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
        $save = (array)$this;

        ksort($save['repositories']);

        unset($save['satis_file']);


        $packageManager = \Config::get('package-manager');
        $save['name'] = $packageManager['package_manager_name'];
        $save['homepage'] = $packageManager['package_manager_homepage'] . '/domains/'.Helpers::getEnvName();

        if (isset($packageManager['whmcs_url'])) {
            $save['whmcs_url'] = $packageManager['whmcs_url'];
        }

       /*
        $save['name'] = 'microweber/packages';
        $save['homepage'] = 'https://packages-satis.microweberapi.com/';
        $save['whmcs_url'] = 'https://members.microweber.com/';
       */

        $save['require-all'] = true;
        $save['notify-batch'] = "https://installreport.services.microweberapi.com";
        $save['archive'] = [
            'directory'=>'dist',
            'format'=>'zip',
            'skip-dev'=>true,
        ];

        $encoded = json_encode($save, JSON_PRETTY_PRINT);

        file_put_contents($this->satis_file, $encoded);
    }
}
