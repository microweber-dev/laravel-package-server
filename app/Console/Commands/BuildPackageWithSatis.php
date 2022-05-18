<?php

namespace App\Console\Commands;

use App\Helpers\Base;
use App\Helpers\RepositoryMediaProcessHelper;
use App\Helpers\RepositoryPathHelper;
use App\Models\Credential;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use function PHPUnit\Framework\throwException;

class BuildPackageWithSatis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:build-with-satis {--file=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build repository package with saits';

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
     * @return int
     */
    public function handle()
    {

        $file = $this->option('file')[0];

        if (!is_file($file)) {
            $this->error('This is not valid file!');
            return;
        }

        $satisContent = json_decode(file_get_contents($file), true);
        if (empty($satisContent)) {
            $this->error('This is not valid satis file!');
            return;
        }

        if (!isset($satisContent['repositories'][0]['url'])) {
            $this->error('This repositories missing from satis file!');
            return;
        }

        $saitsRepositoryPath = dirname($file) . DIRECTORY_SEPARATOR;
        $satisBinPath = base_path() . '/satis-builder/vendor/composer/satis/bin/satis';
        $satisRepositoryOutputPath = $saitsRepositoryPath . 'output-build';

        // Accept host key on all repositories
        if (Base::familyOs() == 'UNIX') {
            foreach ($satisContent['repositories'] as $repository) {
                // Accept host key
                $parseRepositoryUrl = $repository['url'];
                $parseRepositoryUrl = parse_url($parseRepositoryUrl);
                if (isset($parseRepositoryUrl['host'])) {
                    $hostname = $parseRepositoryUrl['host'];
                    if ($hostname != false) {
                        $acceptHost = shell_exec('
            if ! grep "$(ssh-keyscan ' . $hostname . ' 2>/dev/null)" ~/.ssh/known_hosts > /dev/null; then
                ssh-keyscan ' . $hostname . ' >> ~/.ssh/known_hosts
            fi');
                    }
                }
            }
        }

        if (!is_dir($saitsRepositoryPath)) {
            mkdir($saitsRepositoryPath);
        }

        $satisCommand = [];
        $satisCommand[] = 'php';
        $satisCommand[] = '-d memory_limit=-1 max_execution_time=6000';
        $satisCommand[] = '-c ' . base_path() . '/php.ini';
        $satisCommand[] = $satisBinPath;
        $satisCommand[] = 'build';
        $satisCommand[] = $saitsRepositoryPath . 'satis.json';
        $satisCommand[] = $satisRepositoryOutputPath;


        $composerCacheDir = base_path() . '/composer-cache';
        if (!is_dir($composerCacheDir)) {
            mkdir($composerCacheDir);
        }

        $process = new Process($satisCommand, null, [
            'HOME' => dirname(base_path()),
            'COMPOSER_CACHE_DIR ' => $composerCacheDir,
            'COMPOSER_MEMORY_LIMIT ' => '-1',
            'COMPOSER_PROCESS_TIMEOUT ' => 100000,
            'COMPOSER_HOME' => $saitsRepositoryPath
        ]);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $process->mustRun();
        $output = $process->getOutput();

        $packagesJsonFilePath = $satisRepositoryOutputPath . '/packages.json';
        if (!is_file($packagesJsonFilePath)) {
            throw new \Exception('Build failed. packages.json missing.');
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
                    $preparedPackageVerions = [];
                    foreach ($packageVersions as $packageVersionKey => $packageVersion) {

                        if (strpos($packageVersionKey, 'dev') !== false) {
                            continue;
                        }

                        $packageVersion = RepositoryMediaProcessHelper::preparePackageMedia($packageVersion, $satisRepositoryOutputPath);

                        /*  if (isset($packageVersion['name'])) {
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

                          if (isset($packageVersion['extra']['_meta']['readme'])) {
                              $lastVersionMetaData['readme'] = $packageVersion['extra']['_meta']['readme'];
                          }*/

                        $preparedPackageVerions[$packageVersionKey] = $packageVersion;
                    }
                    $preparedPackages[$packageKey] = $preparedPackageVerions;
                }
            }

            $foundedPackages = array_merge($foundedPackages, $preparedPackages);
        }

        rmdir_recursive($satisRepositoryOutputPath . DIRECTORY_SEPARATOR . 'include');

        file_put_contents($satisRepositoryOutputPath . DIRECTORY_SEPARATOR . 'packages.json', json_encode([
                'packages' => $foundedPackages
            ], JSON_PRETTY_PRINT)
        );

        return 0;
    }
}
