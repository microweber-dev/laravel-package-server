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

        return view('home', [
            'repositories' => $repositories
        ]);
    }
}
