<?php

namespace App\Console\Commands;

use App\Helpers\Base;
use App\Models\Package;
use Illuminate\Console\Command;

class CalculatePackageSizes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:calculate-sizes-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $packages = Package::all();
        if ($packages == null) {
            return false;
        }

        foreach ($packages as $package) {
            $json = $package->package_json;
            if (is_array($json)) {
                $versions = end($json);
                $allSizes = 0.0;
                foreach ($versions as $version) {
                    $allSizes = $allSizes + $this->getVersionFilesize($version);
                }
                $package->all_versions_filesize = $allSizes;
                $package->last_version_filesize = $this->getVersionFilesize(end($versions));
                $package->save();
            }
        }

        return 0;
    }

    private function getVersionFilesize($version)
    {
        $filesize = 0;
        if (isset($version['dist']['url'])) {
            $url = $version['dist']['url'];
            $url = str_replace('https://example.com/', false, $url);
            $realpath = public_path($url);
            if (is_file($realpath)) {
                $filesize = filesize($realpath);
            }
        }
        return $filesize;
    }
}
