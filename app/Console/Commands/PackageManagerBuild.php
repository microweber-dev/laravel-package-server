<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
        $packageFiles = $this->_readPackageFiles();
        if (!empty($packageFiles)) {
            foreach ($packageFiles as $file) {
                $this->_preparePackage($this->outputDir .'/'. $file);
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

                    if (strpos($packageVersionKey, 'dev') !== false) {
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

        $filesystem = new Filesystem();

        $distShasum = $packageVersion['dist']['shasum'];
        $distUrl = $packageVersion['dist']['url'];
        $distUrlParsed = parse_url($distUrl);
        $packageMainUrl = $distUrlParsed['scheme'] . '://'. $distUrlParsed['host'] . '/';

        if ($distUrlParsed['path']) {

            $distZip = $this->outputDir . $distUrlParsed['path'];
            if (!$filesystem->exists($distZip)) {
                return $packageVersion;
            }
            
            // Create Main Meta Folder
            $mainMetaFolder = $this->outputDir . '/meta/';
            if (!$filesystem->exists($mainMetaFolder)) {
                $filesystem->mkdir($mainMetaFolder);
            }
            
            // Create Meta Folder
            $metaFolder = $this->outputDir . '/meta/' . $distShasum . '/';
            $metaFolderPublicUrl = $packageMainUrl . 'meta/' . $distShasum . '/';
            if (!$filesystem->exists($metaFolder)) {
                $filesystem->mkdir($metaFolder);
            }

            // Unzio package
            $zip = new \ZipArchive();
            $zip->open($distZip);
            $zip->extractTo($metaFolder);
            $zip->close();


            // Set extra
            $finder = new Finder();
            $finder->files()->in($metaFolder)->name(['*.png','*.svg','*.jpg','readme.md','README.md','screenshot.png','screenshot.jpg','screenshot.jpeg','screenshot.gif']);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {

                    // Parse mark down
                    if ($file->getExtension() == 'md') {
                        $parseDown = new \Parsedown();
                        $parseDownHtml = $parseDown->text($file->getContents());
                        if ($parseDownHtml) {
                            file_put_contents($file->getRealPath(), $parseDownHtml);
                        }
                    }

                    $packageVersion['extra']['_meta'][$file->getFilenameWithoutExtension()] = $metaFolderPublicUrl . $file->getFilename();
                }
            }

            // Remove all files without media files
            $finder = new Finder();
            $finder->files()->in($metaFolder)->notName(['*.md', '*.jpg', '*.gif', '*.jpeg', '*.bmp', '*.png', '*.svg', '*.mp4', '*.mov', '*.avi']);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $filesystem->remove($file->getRealPath());
                    continue;
                }
            }


            // Remove all empty folders
            $filesForDelete = [];
            $finder = new Finder();
            $finder->files()->in($metaFolder)->directories();
            if ($finder->hasResults()) {
                foreach ($finder as $folder) {
                    // Check folder files
                    if (!$filesystem->exists($folder->getRealPath())) {
                        continue;
                    }

                    $checkFolder = new Finder();
                    $checkFolder->files()->in($folder->getRealPath());
                    if (!$checkFolder->hasResults()) {
                        // Delete empty folder
                        $filesForDelete[] = $folder->getRealPath();
                        continue;
                    }
                }
            }

            if (!empty($filesForDelete)) {
                foreach ($filesForDelete as $fileForDelete) {
                    $filesystem->remove($fileForDelete);
                }
            }

        }


        return $packageVersion;
    }

    private function _readPackageFiles() {

        $files = [];
        $content = file_get_contents($this->outputDir . '/original-packages.json');
        $packages = json_decode($content, true);

        if (isset($packages['includes']) && !empty($packages['includes'])) {
            foreach ($packages['includes'] as $packageFile=>$package) {
                $files[] = $packageFile;
            }
        }

        return $files;
    }
}
