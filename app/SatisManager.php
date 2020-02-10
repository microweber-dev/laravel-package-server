<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:59 PM
 */

namespace App;


class SatisManager
{

    public $name;
    public $homepage;
    public $whmcs_url;
    public $satis_file;

    public $repositories = [];

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
    }

    public function addNewRepository($data)
    {
       $repositoryKey = $this->getRepositoryKeyByUrl($data['url']);

        $repositoryData = [
            'type'=>$data['type'],
             'url'=>$data['url'],
             'whmcs_product_ids'=>$data['whmcs_product_ids'],
       ];

       if ($repositoryKey) {
           $this->repositories[$repositoryKey] = $repositoryData;
       } else {
           $this->repositories[] = $repositoryData;
       }

    }

    public function getRepositoryKeyByUrl($url) {

        foreach ($this->repositories as $repositoryKey=>$repository) {
            if ($repository['url'] == $url) {
                return $repositoryKey;
            }
        }

        return false;
    }

    public function save()
    {
        $save = (array) $this;
        unset($save['satis_file']);

        $encoded = json_encode($save, JSON_PRETTY_PRINT);

        file_put_contents($this->satis_file, $encoded);
    }

}