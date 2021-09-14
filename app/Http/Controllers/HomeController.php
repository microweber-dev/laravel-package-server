<?php

namespace App\Http\Controllers;

use App\BuildedRepositories;
use App\Helpers;
use App\SatisManager;
use Illuminate\Http\Request;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $satisFile = Helpers::getEnvConfigDir() . 'satis.json';

        $satis = new SatisManager();
        $satis->load($satisFile);
        $repositories = $satis->getRepositories();

        $builded = new BuildedRepositories();

        if (!empty($repositories)) {
            foreach($repositories as &$repository) {
                $repository['build_info'] = $builded->getBuildInfoByUrl($repository['url']);
            }
        }

        return view('home', [
            'repositories' => $repositories
        ]);
    }
}
