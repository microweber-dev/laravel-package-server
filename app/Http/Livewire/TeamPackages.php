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

    public $is_visible = [];
    public $is_paid = [];

    public function render()
    {
        $keyword = $this->keyword;

        $user = auth()->user();
        $userId = $user->id;
        $teamId = $user->currentTeam->id;
        $this->team = $user->currentTeam;

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
                $query->where('clone_status',Package::CLONE_STATUS_SUCCESS);
            })
            ->whereHas('team')
            ->with('package')
            ->with('team')
            ->orderBy('id','DESC')
           ->paginate(15);

        if (!empty($teamPackages)) {
            foreach ($teamPackages->items() as $teamPackage) {
                $this->is_visible[$teamPackage->id] = (int) $teamPackage->is_visible;
                $this->is_paid[$teamPackage->id] = (int) $teamPackage->is_paid;
            }
        }

        return view('livewire.team-packages.index', compact('teamPackages'));

    }

    public function update($id)
    {
        $userId = auth()->user()->id;

        $package = Package::where('id', $id)->where('user_id', $userId)->first();

        dispatch(new ProcessPackageSatis($package->id));

        $this->check_background_job = true;

    }

}
