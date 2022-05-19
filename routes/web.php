<?php

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


Route::namespace('\App\Http\Controllers')->group(function() {

    Route::any('git-worker-webhook', 'GitWorkerWebhookController@index')
        ->middleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->name('git-worker-webhook');

    Route::any('webhook', 'WebhookController@index')
        ->middleware(\Illuminate\Routing\Middleware\ThrottleRequests::class)
        ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->name('webhook');

    Route::any('packages.json', 'PackagesJsonController@index')->name('packages.json');
    Route::any('packages/{slug}/packages.json', 'PackagesJsonController@team')->name('packages.team.packages.json');
    Route::any('packages/{vendor}/{package}.json', 'PackagesJsonController@singlePackage')->name('packages.team.single-package.json');
});

Route::namespace('\App\Http\Controllers')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])->group(function() {
    Route::post('packages/download-notify', 'PackagesJsonController@downloadNotify')->name('packages.download-notify');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function() {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


    Route::any('team-packages', \App\Http\Livewire\TeamPackages::class)->name('team-packages');
    Route::any('team-packages/{id}/edit', \App\Http\Livewire\TeamPackagesEdit::class)->name('team-packages.edit');

    Route::any('my-packages', \App\Http\Livewire\MyPackages::class)->name('my-packages');
    Route::any('my-packages/add', \App\Http\Livewire\MyPackagesEdit::class)->name('my-packages.add');
    Route::any('my-packages/{id}/edit', \App\Http\Livewire\MyPackagesEdit::class)->name('my-packages.edit');
    Route::any('my-packages/{id}/show', \App\Http\Livewire\MyPackagesShow::class)->name('my-packages.show');

});


