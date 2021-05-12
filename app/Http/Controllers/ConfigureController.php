<?php

namespace App\Http\Controllers;

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
        $envPath = app()->environmentFilePath();
        try {
            $envEditor = \DotenvEditor::load($envPath);
            $packageManagerName = $envEditor->getValue('PACKAGE_MANAGER_NAME');
            $packageManagerHomepage = $envEditor->getValue('PACKAGE_MANAGER_HOMEPAGE');
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

        $envPath = app()->environmentFilePath();
        $envEditor = \DotenvEditor::load($envPath);

        $envEditor->setKey('PACKAGE_MANAGER_NAME', $request->post('package_manager_name'))->save();
        $envEditor->setKey('PACKAGE_MANAGER_HOMEPAGE', $request->post('package_manager_homepage'))->save();

        return redirect(route('configure'));
    }
}
