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
    $file = 'compiled_packages/index.html';
    if (is_file($file)) {
        echo file_get_contents($file);
    } else {
        return view('welcome');
    }
});

Route::get('packages.json', 'PackagesController@index');
Route::get('home', 'HomeController@index')->name('home');
