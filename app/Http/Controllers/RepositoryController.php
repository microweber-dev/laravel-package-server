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

        $this->satis = new SatisManager();
        $this->satis->load('../satis.json');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function edit(Request $request)
    {
        $url = '';
        $whmcsProductIds = '';
        $type = '';

        $repositoryData = $this->satis->getRepositoryByUrl($request->input('url'));
        if ($repositoryData) {
            $url = $repositoryData['url'];
            if (isset($repositoryData['whmcs_product_ids'])) {
                $whmcsProductIds = $repositoryData['whmcs_product_ids'];
            }
            $type = $repositoryData['type'];
        }

        return view('repository.edit', [
            'url' => $url,
            'whmcs_product_ids' => $whmcsProductIds,
            'type' => $type
        ]);
    }

    public function save(Request $request)
    {
        $repoUrl = $request->input('url');
        if (empty($repoUrl)) {
            return redirect(route('home'));
        }

        $this->satis->updateOrNewRepository([
           'whmcs_product_ids'=>$request->input('whmcs_product_ids'),
           'url'=>$request->input('url'),
           'type'=>$request->input('type'),
        ]);
        $this->satis->save();

        return redirect(route('home'));
    }

    public function delete(Request $request)
    {
        $this->satis->deleteRepositoryByUrl($request->input('url'));
        $this->satis->save();

        return redirect(route('home'));

    }

}