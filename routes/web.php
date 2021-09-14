<?php

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

Auth::routes();

Route::get('/', function () {


    return view('welcome');

});

Route::get('packages.json', 'PackagesController@index');
Route::get('home', 'HomeController@index')->name('home');

Route::get('add-repo', 'RepositoryController@edit')->name('add-repo');
Route::post('add-repo', 'RepositoryController@save');

Route::get('edit-repo', 'RepositoryController@edit')->name('edit-repo');
Route::post('edit-repo', 'RepositoryController@save');

Route::get('build-repo', 'RepositoryController@build')->name('build-repo');
Route::get('build-repo-run', 'RepositoryController@buildRun')->name('build-repo-run');

Route::get('delete-repo', 'RepositoryController@delete');

Route::get('configure', 'ConfigureController@index')->name('configure');
Route::post('configure', 'ConfigureController@save')->name('configure-save');

Route::get('configure-whmcs', 'WhmcsController@index')->name('configure-whmcs');
Route::post('configure-whmcs', 'WhmcsController@save')->name('configure-whmcs-save');
Route::post('configure-whmcs-connection-status', 'WhmcsController@getConnectionStatus')->name('configure-whmcs-connection-status');
