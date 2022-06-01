<?php

namespace App\Helpers;

use CzProject\GitPhp\Git;

class PackageManagerGitWorker
{

    public static function pushSatis($satisFile = false, $buildSettingsFile = false)
    {
        $gitWorkerRepositoryUrl = env('PACKAGE_MANAGER_WORKER_REPOSITORY');
        $gitWorkerRepositoryUrlParse = parse_url($gitWorkerRepositoryUrl);
        $gitWorkerRepositoryUrl = $gitWorkerRepositoryUrlParse['host'] . $gitWorkerRepositoryUrlParse['path'];

        $gitProvider = env('PACKAGE_MANAGER_WORKER_TYPE');

        if (env('PACKAGE_MANAGER_WORKER_TYPE') == 'github') {
            $gitRunnerRepositoryUrl = 'https://'.env('GITHUB_BOT_USERNAME').':'.env('GITHUB_BOT_PASSWORD').'@' . $gitWorkerRepositoryUrl;
        } else if (env('PACKAGE_MANAGER_WORKER_TYPE') == 'gitlab') {
            $gitRunnerRepositoryUrl = 'https://'.env('GITLAB_BOT_USERNAME').':'.env('GITLAB_BOT_PASSWORD').'@' . $gitWorkerRepositoryUrl;
        } else {
            return false;
        }

        $allWorkersPath = storage_path() . '/package-manager-worker/'.md5($satisFile);
        if (!is_dir($allWorkersPath)) {
            mkdir_recursive($allWorkersPath);
        }

        $workerGitPath = $allWorkersPath . '/'.$gitProvider.'-worker';
        rmdir_recursive($workerGitPath,false);

        $out = shell_exec('cd '.$allWorkersPath.' && git clone --depth 10 ' . $gitRunnerRepositoryUrl . ' ' . $workerGitPath);

        if (env('PACKAGE_MANAGER_WORKER_TYPE') == 'github') {
            shell_exec('cd '.$workerGitPath.' && git config user.email "'.env('GITHUB_BOT_USERNAME').'" &&  git config user.name "'.env('GITHUB_BOT_USERNAME').'"');
        } else if (env('PACKAGE_MANAGER_WORKER_TYPE') == 'gitlab') {
            shell_exec('cd '.$workerGitPath.' && git config user.email "'.env('GITLAB_BOT_USERNAME').'" &&  git config user.name "'.env('GITLAB_BOT_USERNAME').'"');
        }

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

        $satisContent = file_get_contents($satisFile);
        $satisContent = json_decode($satisContent, true);

        $cloneRepositoryName = 'none';
        if (isset($satisContent['repositories'][0]['url'])) {
            $cloneRepositoryUrl = $satisContent['repositories'][0]['url'];
            $cloneRepositoryUrl = parse_url($cloneRepositoryUrl);
            if (isset($cloneRepositoryUrl['path'])) {
                $cloneRepositoryName = $cloneRepositoryUrl['path'];
                $cloneRepositoryName = mb_substr($cloneRepositoryName, 1);
            } else {
                $cloneRepositoryName = $cloneRepositoryUrl;
            }
        }

        $lastCommitId = false;
        if ($repository->hasChanges()) {
            $repository->addAllChanges();
            $repository->commit('Build package: ' . $cloneRepositoryName);
          //  $repository->push();

            $gitPush = self::gitPush($workerGitPath);
            if (isset($gitPush['push']) && $gitPush['push']) {
                $lastCommitId = $repository->getLastCommitId();
            }
        }

        return ['commit_id'=>$lastCommitId];
    }

    public static function gitPush($workerGitPath, $retry = 4) {

        $status = shell_exec('cd '.$workerGitPath.' && git push --all --force 2>&1');

        if (strpos($status, 'remote rejected') !== false) {
            // Retry
            sleep(rand(3,6));
            return self::gitPush($workerGitPath, ($retry - 1));
        }

        if (strpos($status, 'forced update') !== false) {
            return ['push'=>true];
        }

        if ($retry < 0) {
            return ['push'=>false];
        }
    }

}
