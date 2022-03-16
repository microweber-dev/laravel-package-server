<?php

namespace App\Helpers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Str;

class RepositoryMediaProcessHelper
{
    public static function preparePackageMedia($packageVersion, $workDir) {

        $filesystem = new Filesystem();

        $distShasumName = $packageVersion['name'];
        $distShasumName = str_replace('/', '-', $distShasumName);
        $distShasum = Str::slug($distShasumName, '-') . '/' . Str::slug($packageVersion['version'], '-');

        $distUrl = $packageVersion['dist']['url'];
        $distUrlParsed = parse_url($distUrl);
        $packageMainUrl = $distUrlParsed['scheme'] . '://'. $distUrlParsed['host'] . '/';
        if ($distUrlParsed['path']) {

            $distZip = $workDir . $distUrlParsed['path'];

            if (!$filesystem->exists($distZip)) {
                return $packageVersion;
            }

            // Create Main Meta Folder
            $mainMetaFolder = $workDir.  '/meta/';
            if (!$filesystem->exists($mainMetaFolder)) {
                $filesystem->mkdir($mainMetaFolder);
            }

            // Create Meta Folder
            $metaFolder = $workDir . '/meta/' . $distShasum . '/';
            $metaFolderPublicUrl = $packageMainUrl .'meta/' . $distShasum . '/';

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
            $finder->files()->in($metaFolder)->name(['*.svg', 'video.mp4', 'screenshot.png', 'screenshot.jpg','screenshot.jpeg','screenshot.gif']);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    if (file_exists($file->getRealPath())) {
                        $realPathInside = $file->getRealPath();
                        $realPathInside = str_replace($metaFolder,'', $realPathInside);
                        $packageVersion['extra']['_meta'][$file->getFilenameWithoutExtension()] = $metaFolderPublicUrl . $realPathInside;
                    }
                }
            }

            // Check for readme.md
            $markDownReadmeFile = false;
            if (is_file($metaFolder . 'README.md')) {
                $markDownReadmeFile = $metaFolder . 'README.md';
            } else if (is_file($metaFolder . 'readme.md')) {
                $markDownReadmeFile = $metaFolder . 'readme.md';
            }

            // Parse mark down
            if ($markDownReadmeFile) {
                $markDownReadmeFile = realpath($markDownReadmeFile);
                $markDownReadmeContent = file_get_contents($markDownReadmeFile);

                $parseDown = new \Parsedown();
                $parseDownHtml = $parseDown->text($markDownReadmeContent);
                if ($parseDownHtml) {
                    file_put_contents($markDownReadmeFile, $parseDownHtml);
                }

                $realPathInside = str_replace($metaFolder,'', $markDownReadmeFile);
                $packageVersion['extra']['_meta']['readme'] = $metaFolderPublicUrl . $realPathInside;
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
}
