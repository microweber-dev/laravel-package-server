<?php
/**
 * Created by PhpStorm.
 * User: Bojidar
 * Date: 2/10/2020
 * Time: 5:13 PM
 */

namespace App\Http\Controllers;


use App\BuildedRepositories;
use App\Helpers;
use App\Jobs\PackageManagerBuildJob;
use App\SatisManager;
use Composer\Satis\Satis;
use Gitlab\Client as GitlabClient;
use Gitlab\ResultPager as GitlabResultPager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Laravel\Socialite\Facades\Socialite;

class GitSyncController extends Controller
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

    public function save(Request $request) {

        $satisFile = Helpers::getEnvConfigDir() . 'satis.json';

        $satis = new SatisManager();
        $satis->load($satisFile);

        $repositories = $request->post('repositories', false);
        if (!empty($repositories)) {
            foreach($repositories as $repository) {
                if (isset($repository['import']) && $repository['import'] == '1') {
                    $satis->saveRepository([
                        'whmcs_product_ids' => '',
                        'url' => $repository['url'],
                        'category' => 'templates',
                        'type' => ['VCS'],
                    ]);
                }
            }
            $satis->save();
        }

        return redirect(route('home'));
    }

    public function authCallback(Request $request, $driver)
    {
        $user = Socialite::driver($driver)->user();

        $projects = [];
        if ($driver == 'gitlab') {
            $client = new GitlabClient();
            $client->authenticate($user->token, GitlabClient::AUTH_OAUTH_TOKEN);

            $pager = new GitlabResultPager($client);
            $projects = $pager->fetchAll($client->projects(), 'all', [['membership' => true]]);
        }

        return view('gitsync.callback', [
            'projects' => $projects
        ]);
    }

}
