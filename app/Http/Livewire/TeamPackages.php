<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\Team;
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

    public $add_existing_repository_url = '';
    public $add_existing_repository_id = false;
    public $show_add_team_package_form = false;
    public $add_from_existing = false;
    public $existing_packages_grouped = [];

    public $is_visible = [];
    public $is_paid = [];

    public $confirming_delete_id = true;
    public $confirming_is_visible = true;
    public $confirming_is_paid = true;

    public function showAddTeamPackageForm()
    {
        $this->show_add_team_package_form = true;
    }

    public function hideAddTeamPackageForm()
    {
        $this->show_add_team_package_form = false;
    }

    public function render()
    {
        $keyword = $this->keyword;

        $user = auth()->user();
        $userId = $user->id;
        $teamId = $user->currentTeam->id;
        $this->team = $user->currentTeam;

        $getExistingPackages = Package::select(['team_owner_id','name','type','description','repository_url','id'])
            ->userHasAccess()
            ->get();

        if ($getExistingPackages != null) {
            foreach ($getExistingPackages as $existingPackage) {

                if (in_array($teamId, $existingPackage->teamIdsAsArray())) {
                    continue;
                }

                $groupName = 'All';
                if (!empty($existingPackage->type)) {
                    $groupName = $existingPackage->type;
                }
                $this->existing_packages_grouped[$groupName][$existingPackage->id] = [
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

                       $this->confirming_is_visible = false;
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

                       $this->confirming_is_paid = false;
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

    public function addTeamPackage()
    {
        $user = auth()->user();
        $teamId = $user->currentTeam->id;

        if ($this->add_from_existing) {
            if ($this->add_existing_repository_id) {

                $package = Package::where('id',$this->add_existing_repository_id)
                    ->userHasAccess()
                    ->first();

                if ($package !== null) {
                    $findTeamPacakge = TeamPackage::where('team_id', $teamId)->where('package_id', $package->id)->first();
                    if ($findTeamPacakge == null) {
                        $teamPackage = new TeamPackage();
                        $teamPackage->package_id = $package->id;
                        $teamPackage->team_id = $teamId;
                        $teamPackage->is_visible = 1;
                        $teamPackage->save();
                    }
                }
            }
        }

        if ($this->add_existing_repository_url) {
            return redirect(route('my-packages.add') . '?repository_url=' . trim($this->add_existing_repository_url).'&team_id=' . $teamId.'&team_owner_id='.$teamId);
        }

        $this->show_add_team_package_form = false;
        $this->add_from_existing = false;
        $this->add_existing_repository_id = false;
    }


    public function confirmDelete($id)
    {
        $this->confirming_delete_id = $id;
    }

    public function confirmIsVisible($id)
    {
        $this->confirming_is_visible = $id;
    }

    public function confirmIsPaid($id)
    {
        $this->confirming_is_paid = $id;
    }

    public function delete($id)
    {
        $user = auth()->user();
        $team = $user->currentTeam;

        if (!$user->hasTeamRole($team, 'admin')) {
           return [];
        }

        $findTeamPackage = TeamPackage::where('id', $id)->where('team_id', $team->id)->first();
        $findTeamPackage->delete();
    }

}
