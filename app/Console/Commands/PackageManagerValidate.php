<?php

namespace App\Console\Commands;

use App\Helpers;
use Illuminate\Console\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class PackageManagerValidate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-manager:validate {--domains-dir=domains}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package manager validate.';

    protected $domainsDir = 'domains';
    protected $outputDir = 'public';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $domainsDir = $this->option('domains-dir');
        $domainsDir = trim($domainsDir);
        if (!empty($domainsDir)) {
            if (is_dir('public/' . $domainsDir)) {
                $this->domainsDir = $domainsDir;
            }
        }

        $packageFiles = $this->_readPackageFiles();

        if (!empty($packageFiles)) {
            foreach ($packageFiles as $file) {
                try {
                    $this->_validatePackage($file);
                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            }
        }
    }

    private function _validatePackage($file) {

        $notValidated=0;
        $validated=0;
        $all=0;
        $content = file_get_contents($file);
        $package = json_decode($content, true);

        if (isset($package['packages']) && !empty($package['packages'])) {
            foreach ($package['packages'] as $packageKey=>$packageVersions) {
                foreach ($packageVersions as $packageVersionKey=>$packageVersion) {
                    if (strpos($packageVersionKey, 'dev') !== false) {
                        continue;
                    }

                    $all++;
                    if ($this->_remoteFileExists($packageVersion['dist']['url'])) {
                        $validated++;
                    } else {
                        $notValidated++;
                    }

                    if (isset($packageVersion['extra']['_meta'])) {
                        foreach ($packageVersion['extra']['_meta'] as $checkMeta) {
                            if ($this->_remoteFileExists($checkMeta)) {
                                $validated++;
                            } else {
                                $notValidated++;
                            }
                            $all++;
                        }
                    }
                }
            }
        }

        echo 'validated: ' . $validated . PHP_EOL;
        echo 'notValidated: ' . $notValidated . PHP_EOL;
        echo 'all: ' . $all . PHP_EOL;
    }

    private function _remoteFileExists($url) {

        $curl = curl_init($url);

        //don't fetch the actual page, you only want to check the connection is ok
        curl_setopt($curl, CURLOPT_NOBODY, true);

        //do request
        $result = curl_exec($curl);

        $ret = false;

        //if request did not fail
        if ($result !== false) {
            //if request was ok, check response code
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($statusCode == 200) {
                $ret = true;
            }
        }

        curl_close($curl);

        return $ret;
    }

    private function _readPackageFiles() {

        $files = [];
        $packagesFile = dirname(dirname(dirname(__DIR__))) .'/'. $this->outputDir .'/'.$this->domainsDir.'/'. Helpers::getEnvName() . '/original-packages.json';

        if (!is_file($packagesFile)) {
            return false;
        }

        $content = file_get_contents($packagesFile);
        $packages = json_decode($content, true);

        if (isset($packages['includes']) && !empty($packages['includes'])) {
            foreach ($packages['includes'] as $packageFile=>$package) {
                $files[] = $this->outputDir .'/'.$this->domainsDir.'/'. Helpers::getEnvName() .'/'. $packageFile;
            }
        }

        return $files;
    }
}
