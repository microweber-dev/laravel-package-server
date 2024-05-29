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
                    $packageWhitelistedVersions[] = [
                        'version' => $packageVersion['version'],
                        'packageFolder' => $packageNameSlug,
                        'versionFolder'=> str_replace('.', '', $packageVersion['version'])
                    ];
                }
                $metaPackagePath = (base_path() . '/public/meta/' . $packageNameSlug);
                if (!is_dir($metaPackagePath)) {
                    $this->info('No such directory: ' . $metaPackagePath);
                    continue;
                }
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
