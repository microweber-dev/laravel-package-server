<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:13 PM
 */

namespace App\Http\Controllers;


use App\Jobs\PackageManagerBuildJob;
use App\SatisManager;
use Composer\Satis\Satis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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
        $whmcsProductIds = [];
        $type = '';
        $category = '';
        $previewImage = false;

        $repositoryData = $this->satis->getRepositoryByUrl($request->input('url'));
        if ($repositoryData) {

            $url = $repositoryData['url'];
            $type = $repositoryData['type'];

            if (isset($repositoryData['whmcs_product_ids'])) {
                $whmcsProductIds = explode(',', $repositoryData['whmcs_product_ids']);
            }

            if (isset($repositoryData['category'])) {
                $category = $repositoryData['category'];
            }
        }

        $whmcsProductsTypes = [];
        $getWhmcsProducts = \Whmcs::GetProducts();

        if (isset($getWhmcsProducts['products']['product'])) {
            $whmcsProducts = $getWhmcsProducts['products']['product'];
            foreach($whmcsProducts as $whmcsProduct) {
                $whmcsProductsTypes[$whmcsProduct['type']][] = $whmcsProduct;
            }
        }

        return view('repository.edit', [
            'url' => $url,
            'whmcs_products_types' => $whmcsProductsTypes,
            'whmcs_product_ids' => $whmcsProductIds,
            'category' => $category,
            'type' => $type
        ]);
    }

    public function save(Request $request)
    {
        $repoUrl = $request->input('url');
        if (empty($repoUrl)) {
            return redirect(route('home'));
        }

        $this->satis->saveRepository([
           'whmcs_product_ids'=>implode(',', $request->input('whmcs_product_ids')),
           'url'=>$request->input('url'),
           'category'=>$request->input('category'),
           'type'=>$request->input('type'),
        ]);
        $this->satis->save();

        return redirect(route('home'));
    }

    public function build(Request $request)
    {
        return view('repository.build', [
            'show_log' => $request->get('show_log', 0)
        ]);
    }

    public function buildRun()
    {

        @unlink(base_path() . '/public/build-packages-output.log');

        PackageManagerBuildJob::dispatch();

        return redirect(route('build-repo'). '?show_log=1');
    }

    public function delete(Request $request)
    {
        $this->satis->deleteRepositoryByUrl($request->input('url'));
        $this->satis->save();

        return redirect(route('home'));

    }

}
