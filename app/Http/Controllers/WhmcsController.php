<?php

namespace App\Http\Controllers;

use App\SatisManager;
use Illuminate\Http\Request;

class WhmcsController extends Controller
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
        return view('whmcs.index');
    }
}
