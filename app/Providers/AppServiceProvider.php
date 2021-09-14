<?php

namespace App\Providers;

use App\Helpers;
use Illuminate\Support\ServiceProvider;
use PHPUnit\TextUI\Help;

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
        $envConfigDir = Helpers::getEnvConfigDir();
        $packageServerConfig = @include($envConfigDir .DIRECTORY_SEPARATOR. 'package-server.php');
        if (isset($packageServerConfig['installed']) && $packageServerConfig['installed'] == 1) {
            $envConfigDirScan = scandir($envConfigDir);
            foreach ($envConfigDirScan as $envConfigFile) {

                if ($envConfigFile === '.' || $envConfigFile === '..') {
                    continue;
                }

                $envName = pathinfo($envConfigFile, PATHINFO_FILENAME);
                $envArray = include($envConfigDir . DIRECTORY_SEPARATOR . $envConfigFile);

                \Config::set($envName, $envArray);
            }
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
