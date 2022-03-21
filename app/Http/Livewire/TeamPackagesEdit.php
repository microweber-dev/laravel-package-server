<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\Team;
use App\Models\TeamPackage;
use App\Rules\CanAddRepositoryToTeamRule;
use App\WhmcsManager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class TeamPackagesEdit extends Component
{
    use AuthorizesRequests;

    public $team_package_id;
    public $package_id;
    public $is_visible;
    public $is_paid;
    public $whmcs_primary_product_id = [];
    public $whmcs_product_ids = [];
    public $whmcs_product_types;
    public $repository_url;
    public $buy_url_from;
    public $buy_url;

    public function render()
    {


        return view('livewire.team-packages.edit');
    }

    public function mount($id = false)
    {
        $user = auth()->user();
        $teamId = $user->currentTeam->id;

        $findTeamPackage = TeamPackage::where('id', $id)->with('package')->with('team')->where('team_id', $teamId)->first();
        if ($findTeamPackage == null) {
            return abort(404, "Package  not found");
        }

        $teamSettings = $findTeamPackage->team->settings()->get();

        $whmcsManger = new WhmcsManager($teamSettings);
        try {
            $whmcsProducts = $whmcsManger->getProducts();
        } catch (\Exception $e) {
            $whmcsProducts = [];
        }
        $whmcsProductsTypes = [];
        if (isset($whmcsProducts['products']['product'])) {
            foreach ($whmcsProducts['products']['product'] as $product) {
                $whmcsProductsTypes[$product['type']][] = $product;
            }
        }

        $this->whmcs_product_types = $whmcsProductsTypes;

        if (is_array($findTeamPackage->whmcs_product_ids)) {
            $this->whmcs_product_ids = $findTeamPackage->whmcs_product_ids;
        }

        if (!empty($findTeamPackage->whmcs_primary_product_id)) {
            $this->whmcs_primary_product_id = $findTeamPackage->whmcs_primary_product_id;
        }

        $this->team_package_id = $findTeamPackage->id;
        $this->is_visible = $findTeamPackage->is_visible;
        $this->is_paid = $findTeamPackage->is_paid;
        $this->package_id = $findTeamPackage->package->id;
        $this->repository_url = $findTeamPackage->package->repository_url;
        $this->buy_url_from = $findTeamPackage->package->buy_url_from;
        $this->buy_url = $findTeamPackage->package->buy_url;

    }

    public function edit()
    {
        $findTeamPackage = TeamPackage::where('id', $this->team_package_id)->first();
        if ($findTeamPackage == null) {
            return abort(404, "Package  not found");
        }

        $findTeamPackage->is_visible = $this->is_visible;
        $findTeamPackage->is_paid = $this->is_paid;
        $findTeamPackage->whmcs_product_ids = $this->whmcs_product_ids;
        $findTeamPackage->whmcs_primary_product_id = $this->whmcs_primary_product_id;
        $findTeamPackage->buy_url_from = $this->buy_url_from;
        $findTeamPackage->buy_url = $this->buy_url;
        $findTeamPackage->save();

        $this->redirect(route('team-packages'));
    }
}
