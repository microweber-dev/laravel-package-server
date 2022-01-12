<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\TeamPackage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class TeamPackages extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $team;
    public $keyword = '';
    public $check_background_job = false;

    public $show_add_team_package_form = false;
    public $add_from_existing = false;
    public $existing_packages_grouped = [];

    public $is_visible = [];
    public $is_paid = [];

    public function showAddTeamPackageForm()
    {
        $this->show_add_team_package_form = true;
    }

    public function render()
    {
        $keyword = $this->keyword;

        $user = auth()->user();
        $userId = $user->id;
        $teamId = $user->currentTeam->id;
        $this->team = $user->currentTeam;

        $getExistingPackages = Package::select(['name','type','description','repository_url','id'])->get();
        if ($getExistingPackages != null) {
            foreach ($getExistingPackages as $existingPackage) {
                $groupName = 'All';
                if (!empty($existingPackage->type)) {
                    $groupName = $existingPackage->type;
                }
                $this->existing_packages_grouped[$groupName][] = [
                    'name'=>$existingPackage->displayName(),
                    'id'=>$existingPackage->id
                ];
            }
        }

        // Is visible
        if (!empty($this->is_visible)) {
           foreach ($this->is_visible as $packageId=>$isVisible) {
               $isVisible = intval($isVisible);
               $findTeamPackageEdit = TeamPackage::where('team_id', $teamId)
                   ->where('id', $packageId)
                   ->first();
               if ($findTeamPackageEdit != null) {
                   if (((int) $findTeamPackageEdit->is_visible) != $isVisible) {
                       $findTeamPackageEdit->is_visible = $isVisible;
                       $findTeamPackageEdit->save();
                   }
               }
           }
        }

        // Is paid
        if (!empty($this->is_paid)) {
           foreach ($this->is_paid as $packageId=>$isPaid) {
               $isPaid = intval($isPaid);
               $findTeamPackageEdit = TeamPackage::where('team_id', $teamId)
                   ->where('id', $packageId)
                   ->first();
               if ($findTeamPackageEdit != null) {
                   if (((int) $findTeamPackageEdit->is_paid) != $isPaid) {
                       $findTeamPackageEdit->is_paid = $isPaid;
                       $findTeamPackageEdit->save();
                   }
               }
           }
        }

        $teamPackages = TeamPackage::where('team_id', $teamId)
            ->whereHas('package', function (Builder $query) {
           //     $query->where('clone_status',Package::CLONE_STATUS_SUCCESS);
            })
            ->whereHas('team')
            ->with('package')
            ->with('team')
            ->orderBy('id','DESC')
           ->paginate(100);

        if (!empty($teamPackages)) {
            foreach ($teamPackages->items() as $teamPackage) {
                $this->is_visible[$teamPackage->id] = (int) $teamPackage->is_visible;
                $this->is_paid[$teamPackage->id] = (int) $teamPackage->is_paid;
            }
        }

        return view('livewire.team-packages.index', compact('teamPackages'));

    }


    public function packageUpdate($id)
    {
        $user = auth()->user();
        $teams = $user->allTeams();

        $userAdminInTeams = [];
        foreach ($teams as $team) {
            if ($user->hasTeamRole($team, 'admin')) {
                $userAdminInTeams[] = $team->id;
            }
        }

        $package = Package::where('id', $id)->with('teams')->first();
        if (!in_array($package->team_owner_id, $userAdminInTeams)) {
            return [];
        }

        dispatch(new ProcessPackageSatis($package->id));

        $this->check_background_job = true;

    }

}
