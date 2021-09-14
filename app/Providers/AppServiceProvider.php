<?php

namespace App\Providers;

use App\Helpers;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $envName = Helpers::getEnvName();
        $envConfigDir = dirname(dirname(__DIR__)) . '/config/' . $envName . '/';
        $envConfigDirScan = scandir($envConfigDir);
        foreach ($envConfigDirScan as $envConfigFile) {

            if($envConfigFile === '.' || $envConfigFile === '..') {
                continue;
            }

            $envName = pathinfo($envConfigFile, PATHINFO_FILENAME);
            $envArray = include($envConfigDir . DIRECTORY_SEPARATOR .  $envConfigFile);

            \Config::set($envName, $envArray);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
