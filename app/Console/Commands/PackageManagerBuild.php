<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PackageManagerBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-manager:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package manager build/rebuild.';

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
        $outputDir = 'public/compiled_packages';

        $process = new Process([
            'php',
            'vendor/composer/satis/bin/satis',
            'build',
            './satis.json',
            $outputDir,
            '--stats'
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $process->getOutput();

        $packageFiles = $this->_readPackageFiles($outputDir);
        if (!empty($packageFiles)) {
            foreach ($packageFiles as $file) {
                $this->_preparePackage($outputDir .'/'. $file);
            }
        }
    }

    private function _preparePackage($file) {

        $preparedPackages = [];
        $content = file_get_contents($file);
        $package = json_decode($content, true);
        if (isset($package['packages']) && !empty($package['packages'])) {
            foreach ($package['packages'] as $packageKey=>$packageVersions) {

                $preparedPackageVerions = [];
                foreach ($packageVersions as $packageVersionKey=>$packageVersion) {

                    if ($packageVersionKey == 'dev-master') {
                        continue;
                    }

                    $packageVersion = $this->_preparePackageMedia($packageVersion);

                    $preparedPackageVerions[$packageVersionKey] = $packageVersion;
                }

                $preparedPackages[$packageKey] = $preparedPackageVerions;
            }
        }

        $encodeNewPackage = json_encode(['packages'=>$preparedPackages], JSON_PRETTY_PRINT);
        file_put_contents($file, $encodeNewPackage);
    }

    private function _preparePackageMedia($packageVersion) {

        $packageVersion['xwd123'] = 'fwafwafwawfa';
        $packageVersion['fawfwa'] = '3'; 

        return $packageVersion;
    }

    private function _readPackageFiles($outputDir) {

        $files = [];
        $content = file_get_contents($outputDir . '/packages.json');
        $packages = json_decode($content, true);

        if (isset($packages['includes']) && !empty($packages['includes'])) {
            foreach ($packages['includes'] as $packageFile=>$package) {
                $files[] = $packageFile;
            }
        }

        return $files;
    }
}