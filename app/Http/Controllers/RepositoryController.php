<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:13 PM
 */

namespace App\Http\Controllers;


use App\SatisManager;
use Composer\Satis\Satis;
use Illuminate\Http\Request;

class RepositoryController extends Controller
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
        return view('repository.add');
    }

    public function add(Request $request)
    {
        $satis = new SatisManager();
        $satis->load('../satis.json');
        $satis->addNewRepository([
           'whmcs_product_ids'=>$request->input('whmcs_product_ids'),
           'url'=>$request->input('url'),
           'type'=>$request->input('type'),
        ]);
        $satis->save();

    }

}