<?php

namespace App\Http\Controllers;

use App\SatisManager;
use Illuminate\Http\Request;

class WhmcsController extends Controller
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
        $invoice = \Whmcs::GetProducts();

        dd($invoice);

        return view('whmcs.index');
    }
}
