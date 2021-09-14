<?php
namespace App;

class Helpers
{
    public static function getEnvName()
    {
        $environment = \Request::server("SERVER_NAME");

        if (php_sapi_name() == 'cli') {
            $environment = \App::environment();
        }

        return $environment;
    }

    public static function getEnvConfigDir()
    {
        return dirname(__DIR__) . '/config/' . self::getEnvName() . '/';
    }

    public static function getValuesFromEnvConfig($config = 'app')
    {
        return \Config::get($config);
    }

    public static function setValuesToEnvConfig($config = 'app', $values)
    {
        $overwriteConfig = \Config::get('package-manager');
        if (!empty($values)) {
            foreach ($values as $key=>$value) {
                $overwriteConfig[$key] = $value;
            }
        }

        file_put_contents(Helpers::getEnvConfigDir() . $config . '.php', '<?php return ' . var_export($overwriteConfig, true).';');
    }
}
