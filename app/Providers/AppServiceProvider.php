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
        $envConfigDir = Helpers::getEnvConfigDir();

        $packageServerConfig = @include($envConfigDir .DIRECTORY_SEPARATOR. 'package-manager.php');
        if (isset($packageServerConfig['installed']) && $packageServerConfig['installed'] == 1) {
            $envConfigDirScan = scandir($envConfigDir);
            foreach ($envConfigDirScan as $envConfigFile) {

                if ($envConfigFile === '.' || $envConfigFile === '..') {
                    continue;
                }

                $configName = pathinfo($envConfigFile, PATHINFO_FILENAME);
                ob_start();
                $envArray = include($envConfigDir . DIRECTORY_SEPARATOR . $envConfigFile);
                ob_clean();
                \Config::set($configName, $envArray);
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
