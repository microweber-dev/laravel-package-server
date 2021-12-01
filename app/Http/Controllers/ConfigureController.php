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
            $packageManagerTemplatesDemoDomain = $packageManager['package_manager_templates_demo_domain'];

        } catch (\Exception $e) {
            $packageManagerHomepage = '';
            $packageManagerName = '';
            $packageManagerTemplatesDemoDomain = '';
        }

        return view('configure.index', [
            'package_manager_templates_demo_domain' => $packageManagerTemplatesDemoDomain,
            'package_manager_name' => $packageManagerName,
            'package_manager_homepage' => $packageManagerHomepage
        ]);
    }

    public function save(Request $request) {

        PackageManagerBuildJob::dispatch();

        $values = [];
        $values['package_manager_name'] = $request->post('package_manager_name');
        $values['package_manager_homepage'] = $request->post('package_manager_homepage');
        $values['package_manager_templates_demo_domain'] = $request->post('package_manager_templates_demo_domain');

        Helpers::setValuesToEnvConfig('package-manager', $values);

        return redirect(route('configure'));
    }
}
