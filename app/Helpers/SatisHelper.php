<?php

namespace App\Helpers;

use Symfony\Component\Process\Process;

class SatisHelper
{
    public static function checkRepositoryIsPrivate($url)
    {
        shell_exec('GIT_TERMINAL_PROMPT=0');
        $process = new Process(['git', 'ls-remote',$url]);
        $process->start();

        if (!$process->isSuccessful()) {
            return true;
        }

        $output = $process->getOutput();
        if (strpos($output, 'HEAD') !== false) {
            $process->stop();
            return false;
        } else {
            $process->stop();
        }

        return true;
    }

    public static function getLatestVersionFromPackage($packages)
    {
        $latestVersion = [];
        if (!empty($packages)) {
            foreach ($packages as $packageKey => $packageVersions) {
                $versions = [];
                foreach ($packageVersions as $packageVersionKey => $packageVersion) {
                    if (strpos($packageVersionKey, 'dev') !== false) {
                        continue;
                    }
                    $versions[$packageVersionKey] = $packageVersion;
                }
                sort($versions);
                $latestVersion = end($versions);
            }
        }

        return $latestVersion;
    }

    public static function getMetaDataFromPackageVersion($packageVersion)
    {
        $lastVersionMetaData = [];
          if (isset($packageVersion['name'])) {
            $lastVersionMetaData['name'] = $packageVersion['name'];
        }

        if (isset($packageVersion['type'])) {
            $lastVersionMetaData['type'] = $packageVersion['type'];
        }

        if (isset($packageVersion['description'])) {
            $lastVersionMetaData['description'] = $packageVersion['description'];
        }

        if (isset($packageVersion['keywords'])) {
            $lastVersionMetaData['keywords'] = $packageVersion['keywords'];
        }

        if (isset($packageVersion['homepage'])) {
            $lastVersionMetaData['homepage'] = $packageVersion['homepage'];
        }

        if (isset($packageVersion['version'])) {
            $lastVersionMetaData['version'] = $packageVersion['version'];
        }

        if (isset($packageVersion['target-dir'])) {
            $lastVersionMetaData['target_dir'] = $packageVersion['target-dir'];
        }

        if (isset($packageVersion['extra']['preview_url'])) {
            $lastVersionMetaData['preview_url'] = $packageVersion['extra']['preview_url'];
        }

        if (isset($packageVersion['extra']['_meta']['screenshot'])) {
            $lastVersionMetaData['screenshot'] = $packageVersion['extra']['_meta']['screenshot'];
        }

        if (isset($packageVersion['extra']['_meta']['screenshot_large'])) {
            $lastVersionMetaData['screenshot_large'] = $packageVersion['extra']['_meta']['screenshot_large'];
        }

        if (isset($packageVersion['extra']['_meta']['readme'])) {
            $lastVersionMetaData['readme'] = $packageVersion['extra']['_meta']['readme'];
        }

        return $lastVersionMetaData;
    }

}
