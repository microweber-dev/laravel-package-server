<?php

use App\Models\Package;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    if (\Illuminate\Support\Facades\Auth::check()) {
        return redirect('team-packages');
    }

    return view('welcome');
});

Route::get('/tests', function () {

    $findPackage = Package::where('id', 36)->first();
    $packageJson = json_decode($findPackage->package_json, true);


//    $latestVersion = \App\Helpers\SatisHelper::getLatestVersionFromPackage($packageJson['microweber-templates/art']);
//    dd($latestVersion);

});

Route::get('/clear-old-files', function () {

    $latestVersionDistFiles = [];
    $latestVersionMetaFolders = [];
    $getPackages = \App\Models\Package::where('clone_status', Package::CLONE_STATUS_SUCCESS)->get();
    if (!empty($getPackages)) {
        foreach ($getPackages as $package) {
           $packageJson = json_decode($package->package_json, true);
           foreach ($packageJson as $packageName=>$packageVersions) {
               foreach ($packageVersions as $packageVersion) {

                   $realPath = $packageVersion['dist']['url'];
                   $realPath = str_replace('https://example.com/', '', $realPath);
                   $mainPath = public_path(dirname($realPath));
                   $latestVersionDistFiles[$mainPath][] = public_path($realPath);


                   $versionWithoutDots = str_replace('.','', $packageVersion['version']);
                   $metaMainPath = 'meta/'.str_replace('/','-',$packageName);
                   $metaMainPath = public_path($metaMainPath);
                   $metaPath = $metaMainPath . DIRECTORY_SEPARATOR . $versionWithoutDots;

                   $latestVersionMetaFolders[$metaMainPath][] = $metaPath;
               }
           }
        }
    }

    // Delete meta
    if (!empty($latestVersionMetaFolders)) {
        foreach ($latestVersionMetaFolders as $packageMetaPath=>$packageMetaFolders) {

            if (!is_dir($packageMetaPath)) {
                continue;
            }

            $pathsForDelete = [];
            $foundedPaths = [];
            $dirs = scandir($packageMetaPath);
            if (!empty($dirs)) {
                foreach ($dirs as $dir) {
                    if ($dir != '.' && $dir != '..') {
                        $dirPath = $packageMetaPath . DIRECTORY_SEPARATOR . $dir;
                        if (is_dir($dirPath)) {
                            if (in_array($dirPath, $packageMetaFolders)) {
                                $foundedPaths[] = $dirPath;
                            } else {
                                $pathsForDelete[] = $dirPath;
                            }
                        }
                    }
                }
            }

            if (!empty($pathsForDelete)) {
                foreach ($pathsForDelete as $pathForDelete) {

                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($pathForDelete, RecursiveDirectoryIterator::SKIP_DOTS),
                        RecursiveIteratorIterator::CHILD_FIRST
                    );

                    foreach ($files as $fileinfo) {
                        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                        $todo($fileinfo->getRealPath());
                    }

                    rmdir($pathForDelete);

                    echo 'Deleted meta folder: '.$pathForDelete.'<br>';
                }
            }
        }
    }



    // Delete dits
    if (!empty($latestVersionDistFiles)) {
        foreach ($latestVersionDistFiles as $packageDistPath=>$packageDistFiles) {

            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->in($packageDistPath);
            if ($finder->hasResults()) {

                $filesForDelete = [];
                $foundedFiles = [];
                foreach ($finder as $fileOrFolder) {
                    if (!$fileOrFolder->isDir()) {
                        if (in_array($fileOrFolder->getRealPath(), $packageDistFiles)) {
                            $foundedFiles[] = $fileOrFolder->getRealPath();
                        } else {
                            $filesForDelete[] = $fileOrFolder->getRealPath();
                        }
                    }
                }

                if (!empty($filesForDelete)) {
                    foreach ($filesForDelete as $fileForDelete) {
                        unlink($fileForDelete);
                        echo 'Delete dist: '.$fileForDelete.'<br>';
                    }
                }
            }
        }
    }

});



Route::namespace('\App\Http\Controllers')->group(function() {

    Route::middleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->group(function() {

        Route::any('git-worker-webhook', 'GitWorkerWebhookController@index')->name('git-worker-webhook');
        Route::any('git-notification-webhook', 'GitWorkerWebhookController@notification')->name('git-webhook-notification');
        Route::any('webhook', 'WebhookController@index')->name('webhook');

    });

    // Detect by domain
    Route::get('licenses/check', 'LicenseController@checkFromDomain')->name('license-check');
    // Detect from slug
    Route::get('licenses/{slug}/check', 'LicenseController@check')->name('license-check');

    Route::get('packages/download-private', 'PackagesJsonController@downloadPrivatePackage')->name('packages.download-private');

    Route::any('packages.json', 'PackagesJsonController@index')->name('packages.json');
    Route::any('packages/{slug}/packages.json', 'PackagesJsonController@team')->name('packages.team.packages.json');
    Route::any('packages/{slug}/{package}.json', 'PackagesJsonController@singlePackage')->name('packages.team.single-packages.json');
});

Route::namespace('\App\Http\Controllers')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function() {
    Route::post('packages/download-notify', 'PackageInstallNotifyController@downloadNotify')->name('packages.download-notify');
    Route::post('packages/download-notify-private', 'PackageInstallNotifyController@downloadNotifyPrivate')->name('packages.download-notify-private');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function() {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::any('team-packages', \App\Http\Livewire\TeamPackages::class)->name('team-packages');
    Route::any('team-packages/{id}/edit', \App\Http\Livewire\TeamPackagesEdit::class)->name('team-packages.edit');

    Route::any('my-packages', \App\Http\Livewire\MyPackages::class)->name('my-packages');
    Route::any('my-packages/add', \App\Http\Livewire\MyPackagesEdit::class)->name('my-packages.add');
    Route::any('my-packages/bulk-add', \App\Http\Livewire\MyPackagesBulkAdd::class)->name('my-packages.bulk-add');
    Route::any('my-packages/{id}/edit', \App\Http\Livewire\MyPackagesEdit::class)->name('my-packages.edit');
    Route::any('my-packages/{id}/show', \App\Http\Livewire\MyPackagesShow::class)->name('my-packages.show');

});


