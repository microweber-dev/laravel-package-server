<?php

namespace App;

use Illuminate\Support\Facades\Config;

class WhmcsManager
{
    public $config = [];

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function getProducts() {

        if (isset($this->config['whmcs_auth_type'])) {
            Config::set('whmcs.auth_type', $this->config['whmcs_auth_type']);
        }

        if (isset($this->config['whmcs_url'])) {
            Config::set('whmcs.apiurl', $this->config['whmcs_url'] . '/includes');
        }

        if (isset($this->config['whmcs_api_identifier'])) {
            Config::set('whmcs.api.identifier', $this->config['whmcs_api_identifier']);
        }

        if (isset($this->config['whmcs_api_secret'])) {
            Config::set('whmcs.api.secret', $this->config['whmcs_api_secret']);
        }

        if (isset($this->config['whmcs_username'])) {
            Config::set('whmcs.password.username', $this->config['whmcs_username']);
        }

        if (isset($this->config['whmcs_password'])) {
            Config::set('whmcs.password.password', $this->config['whmcs_password']);
        }

        $products = \Whmcs::GetProducts();

        return $products;
    }
}
