<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:13 PM
 */

namespace App\Http\Controllers;


use App\BuildedRepositories;
use App\Helpers;
use App\Jobs\PackageManagerBuildJob;
use App\SatisManager;
use Composer\Satis\Satis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Laravel\Socialite\Facades\Socialite;

class GitSyncController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function authCallback(Request $request, $driver)
    {
        $user = Socialite::driver($driver)->user();
    }

}
