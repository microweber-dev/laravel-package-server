<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DeleteOldPackageFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:delete-old-package-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reduce disk space by deleting old package files';

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

        $getPackages = Package::where('clone_status', Package::CLONE_STATUS_SUCCESS)->get();
        if ($getPackages == null) {
            $this->error('No packages. Time: ' . date('Y-m-d H:i:s'));
            return 0;
        }

        $filesForDelete = [];
        $whitelistedFiles = [];
        foreach ($getPackages as $package) {

            $packageJson = json_decode($package->package_json, true);
            foreach ($packageJson as $packageName=>$packageVersions) {
                $packageWhitelistedVersions = [];
                foreach ($packageVersions as $packageVersion) {
                    $packageNameSlug = str_replace('/', '-', $packageName);
                    $distUrl = $packageVersion['dist']['url'];
                    $distZip = str_replace('https://example.com/dist/microweber-templates/business/', '', $distUrl);
                    $packageWhitelistedVersions[] = [
                        'version' => $packageVersion['version'],
                        'packageFolder' => $packageNameSlug,
                        'distUrl'=>$packageVersion['dist']['url'],
                        'distZip'=>$distZip,
                        'versionFolder'=> str_replace('.', '', $packageVersion['version'])
                    ];
                }
                $packageDistFolder = $packageName;
                $distPackagePath = (base_path() . '/public/dist/' . $packageDistFolder);
                if (!is_dir($distPackagePath)) {
                    $this->info('No such directory: ' . $distPackagePath);
                } else {
                    $distPackagePathVersions = scandir($distPackagePath);
                    foreach ($distPackagePathVersions as $distPackagePathVersionZip) {
                        if ($distPackagePathVersionZip == '.' || $distPackagePathVersionZip == '..') {
                            continue;
                        }
                        $isVersionWhitelisted = false;
                        foreach ($packageWhitelistedVersions as $packageWhitelistedVersion) {
                            if ($packageWhitelistedVersion['distZip'] == $distPackagePathVersionZip) {
                                $isVersionWhitelisted = true;
                                break;
                            }
                        }
                        if ($isVersionWhitelisted) {
                            $whitelistedFiles[] = $distPackagePath . '/' . $distPackagePathVersionZip;
                            continue;
                        }

                        $filesForDelete[] = $distPackagePath . '/' . $distPackagePathVersionZip;
                    }
                }

                $metaPackagePath = (base_path() . '/public/meta/' . $packageNameSlug);
                if (!is_dir($metaPackagePath)) {
                    $this->info('No such directory: ' . $metaPackagePath);
                } else {
                    $metaPackagePathVersions = scandir($metaPackagePath);
                    foreach ($metaPackagePathVersions as $metaPackagePathVersion) {
                        if ($metaPackagePathVersion == '.' || $metaPackagePathVersion == '..') {
                            continue;
                        }
                        $isVersionWhitelisted = false;
                        foreach ($packageWhitelistedVersions as $packageWhitelistedVersion) {
                            if ($metaPackagePathVersion == $packageWhitelistedVersion['versionFolder']) {
                                $isVersionWhitelisted = true;
                                break;
                            }
                        }
                        if ($isVersionWhitelisted) {
                            $whitelistedFiles[] = $metaPackagePath . '/' . $metaPackagePathVersion;
                            continue;
                        }

                        $filesForDelete[] = $metaPackagePath . '/' . $metaPackagePathVersion;

                    }
                }
            }

            if (!empty($filesForDelete)) {
                foreach ($filesForDelete as $fileForDelete) {
                    $this->info('Delete: ' . $fileForDelete);
                    shell_exec('rm -rf ' . $fileForDelete);
                }
            }
        }

        return 0;
    }
}
