<?php

namespace App\Http\Controllers;

use App\Helpers;
use App\Jobs\PackageManagerBuildJob;
use Illuminate\Http\Request;

class ConfigureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            $packageManager = Helpers::getValuesFromEnvConfig('package-manager');
            $packageManagerName = $packageManager['package_manager_name'];
            $packageManagerHomepage = $packageManager['package_manager_homepage'];
        } catch (\Exception $e) {
            $packageManagerHomepage = '';
            $packageManagerName = '';
        }

        return view('configure.index',[
            'package_manager_name' => $packageManagerName,
            'package_manager_homepage' => $packageManagerHomepage
        ]);
    }

    public function save(Request $request) {

        PackageManagerBuildJob::dispatch();

        $values = [];
        $values['package_manager_name'] = $request->post('package_manager_name');
        $values['package_manager_homepage'] = $request->post('package_manager_homepage');

        Helpers::setValuesToEnvConfig('package-manager', $values);

        return redirect(route('configure'));
    }
}
