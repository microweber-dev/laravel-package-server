<?php

namespace App\Helpers;

use CzProject\GitPhp\Git;

class PackageManagerGitWorker
{

    public static function pushSatis($satisFile = false, $buildSettingsFile = false)
    {
        $gitWorkerRepositoryUrl = 'https://gitlab.com/mw-internal/package-manager/package-manager-worker.git';
        $gitWorkerRepositoryUrlParse = parse_url($gitWorkerRepositoryUrl);
        $gitWorkerRepositoryUrl = $gitWorkerRepositoryUrlParse['host'] . $gitWorkerRepositoryUrlParse['path'];

        $gitProvider = 'github';
        if (strpos($gitWorkerRepositoryUrlParse['host'], 'gitlab') !== false) {
            $gitProvider = 'gitlab';
        }

        $gitRunnerRepositoryUrl = 'https://build:glpat-s947uAnt_G-E7Sezwozb@' . $gitWorkerRepositoryUrl;

        $allWorkersPath = storage_path() . '/package-manager-worker/'.md5($satisFile);
        if (!is_dir($allWorkersPath)) {
            mkdir_recursive($allWorkersPath);
        }

        $workerGitPath = $allWorkersPath . '/'.$gitProvider.'-worker';
        rmdir_recursive($workerGitPath,false);

        shell_exec('cd '.$allWorkersPath.' && git clone --depth 10 ' . $gitRunnerRepositoryUrl . ' ' . $workerGitPath);
        shell_exec('cd '.$workerGitPath.' && git config user.email "bot@microweber.com" &&  git config user.name "mw-bot"');

        $git = new Git();
        $repository = $git->open($workerGitPath);

        if ($satisFile) {
            file_put_contents($workerGitPath . '/satis.json', file_get_contents($satisFile));
        } else {
            file_put_contents($workerGitPath . '/time.txt', time());
        }

        if ($buildSettingsFile) {
            file_put_contents($workerGitPath . '/build-settings.json', file_get_contents($buildSettingsFile));
        }

        $lastCommitId = false;
        if ($repository->hasChanges()) {
            $repository->addAllChanges();
            $repository->commit('update');
            $repository->push();
            $lastCommitId = $repository->getLastCommitId();
        }

        return ['commit_id'=>$lastCommitId];
    }

}
