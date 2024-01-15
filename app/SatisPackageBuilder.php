<?php

namespace App;

use App\Helpers\Base;
use App\Helpers\RepositoryMediaProcessHelper;
use App\Helpers\SatisHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SatisPackageBuilder
{
    public static function build($file)
    {
        if (!is_file($file)) {
            throw new \Exception('This is not valid file!');
            return;
        }

        $satisContent = json_decode(file_get_contents($file), true);
        if (empty($satisContent)) {
            throw new \Exception('This is not valid satis file!');
            return;
        }

        if (!isset($satisContent['repositories'][0]['url'])) {
            throw new \Exception('This repositories missing from satis file!');
            return;
        }

        $saitsRepositoryPath = dirname($file) . DIRECTORY_SEPARATOR;
        $satisRepositoryOutputPath = $saitsRepositoryPath . 'output-build';

        /*  $signature = false;
          $callbackUrl = false;
          $buildSettings = [];
          $buildSettingsFile = $saitsRepositoryPath . 'build-settings.json';
          if (is_file($buildSettingsFile)) {
              $buildSettings = json_decode(file_get_contents($buildSettingsFile),true);
              if (isset($buildSettings['runner-config']['signature'])) {
                  $signature = $buildSettings['runner-config']['signature'];
              }
              if (isset($buildSettings['runner-config']['callback-url'])) {
                  $callbackUrl = $buildSettings['runner-config']['callback-url'];
              }
          }*/

        // Accept host key on all repositories
        if (Base::familyOs() == 'UNIX') {

            $performsSshKeyscan = false;
            $sshKeysDir = dirname(dirname(__DIR__)) . '/.ssh/';
            if (isset($_SERVER['HOME'])) {
                $sshKeysDir = $_SERVER['HOME'] . '/.ssh/';
            }

            if(is_dir($sshKeysDir)) {
                $performsSshKeyscan = true;
            } else {
                mkdir($sshKeysDir, 700, true);
                shell_exec('echo -e "StrictHostKeyChecking no\n" >> ~/.ssh/config');
            }

            foreach ($satisContent['repositories'] as $repository) {
                // Accept host key
                $parseRepositoryUrl = $repository['url'];
                $parseRepositoryUrl = parse_url($parseRepositoryUrl);

                if (isset($parseRepositoryUrl['host'])) {
                    $hostname = $parseRepositoryUrl['host'];
                    if ($hostname != false) {
                        if ($performsSshKeyscan) {
                            if (!is_file($sshKeysDir.'known_hosts')) {
                                $acceptHost = shell_exec('ssh-keyscan ' . $hostname . ' >> '.$sshKeysDir.'known_hosts');
                            } else {
                                $acceptHost = shell_exec('
            if ! grep "$(ssh-keyscan ' . $hostname . ' 2>/dev/null)" '.$sshKeysDir.'known_hosts > /dev/null; then
                ssh-keyscan ' . $hostname . ' >> '.$sshKeysDir.'known_hosts
            fi');
                            }
                        } else {

                        }
                    }
                }
            }
        }

        if (!is_dir($saitsRepositoryPath)) {
            mkdir($saitsRepositoryPath);
        }

        $satisConfigFile = $saitsRepositoryPath . 'satis.json';
        $satisBuildLog = $saitsRepositoryPath . 'docker-satis-build.log';

        $shellFile = dirname(__DIR__) . '/run-docker-satis-build.sh';
        $shellCommand = $shellFile;
        $shellCommand .= ' ' . dirname(__DIR__);
        $shellCommand .= ' ' . $satisConfigFile;
        $shellCommand .= ' ' . $satisRepositoryOutputPath;
        $shellCommand .= ' > ' . $satisBuildLog;

        exec($shellCommand);

        $i = 0;
        $maxI = 60;
        $lastLogText = '';
        while (true) {
            if ($i >= $maxI) {
                break;
            }
            $lastLogText = file_get_contents($satisBuildLog) . PHP_EOL;
            if (strpos($lastLogText, 'Writing web view') !== false) {
                sleep(3);
                break;
            }
            $i++;
            sleep(10);
        }

        $packagesJsonFilePath = $satisRepositoryOutputPath . '/packages.json';
        if (!is_file($packagesJsonFilePath)) {
            throw new \Exception('Build failed. packages.json missing. Error: ' . $lastLogText);
        }

        $packagesJson = json_decode(file_get_contents($packagesJsonFilePath), true);
        if (empty($packagesJson)) {
            if (!is_file($packagesJsonFilePath)) {
                throw new \Exception('Build failed. packages.json is empty.');
            }
        }

        $includedPackageFiles = [];
        foreach ($packagesJson['includes'] as $includeKey => $includes) {
            $includedPackageFiles[] = $satisRepositoryOutputPath . '/' . $includeKey;
        }

        $lastVersionMetaData = [];

        $foundedPackages = [];
        foreach ($includedPackageFiles as $file) {

            $includedPackageContent = json_decode(file_get_contents($file), true);

            $preparedPackages = [];
            if (!empty($includedPackageContent['packages'])) {
                foreach ($includedPackageContent['packages'] as $packageKey => $packageVersions) {
                    $preparedPackageVersions = [];
                    foreach ($packageVersions as $packageVersionKey => $packageVersion) {

                        if (strpos($packageVersionKey, 'dev') !== false) {
                            continue;
                        }

                        $packageVersion = RepositoryMediaProcessHelper::preparePackageMedia($packageVersion, $satisRepositoryOutputPath);

                        $preparedPackageVersions[$packageVersionKey] = $packageVersion;
                    }
                    $preparedPackages[$packageKey] = $preparedPackageVersions;
                }
            }

            $foundedPackages = array_merge($foundedPackages, $preparedPackages);
        }

        rmdir_recursive($satisRepositoryOutputPath . DIRECTORY_SEPARATOR . 'include', false);

        file_put_contents($satisRepositoryOutputPath . DIRECTORY_SEPARATOR . 'packages.json', json_encode([
                'packages' => $foundedPackages
            ], JSON_PRETTY_PRINT)
        );

        return ['output_path'=>$satisRepositoryOutputPath];
    }
}
