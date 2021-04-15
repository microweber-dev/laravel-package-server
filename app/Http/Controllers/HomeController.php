<?php

namespace App\Http\Controllers;

use App\BuildedRepositories;
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
        $satis = new SatisManager();
        $satis->load('../satis.json');
        $repositories = $satis->getRepositories();

        $builded = new BuildedRepositories();
        $builded = $builded->get();
        if (!empty($builded) && !empty($repositories)) {
            foreach($repositories as &$repository) {
                $repository['build_info'] = false;
                foreach ($builded as $repositoryName => $repositoryVersions) {
                    if (strpos($repository['url'], $repositoryName) !== false) {
                        $lastVersion = end($repositoryVersions);
                        $repository['build_info'] = $lastVersion;
                        break;
                    }
                }
            }
        }

        return view('home', [
            'repositories' => $repositories
        ]);
    }
}
