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

        $this->satis->saveRepository([
           'whmcs_product_ids'=>$request->input('whmcs_product_ids'),
           'url'=>$request->input('url'),
           'type'=>$request->input('type'),
        ]);
        $this->satis->save();

        return redirect(route('home'));
    }

    public function build()
    {
        $log = '';

       /* $log .= Artisan::call('package-manager:change-satis-schema');
        $log .= execCmd('vendor/composer/satis/bin/satis build ./satis.json public --stats -n');
        $log .= execCmd('mv public/packages.json public/original-packages.json');
        $log .= Artisan::call('package-manager:build');*/

        return view('repository.build', [
            'log' => $log
        ]);
    }

    public function delete(Request $request)
    {
        $this->satis->deleteRepositoryByUrl($request->input('url'));
        $this->satis->save();

        return redirect(route('home'));

    }

}