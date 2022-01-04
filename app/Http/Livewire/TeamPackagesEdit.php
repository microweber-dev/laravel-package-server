<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\Team;
use App\Models\TeamPackage;
use App\Rules\CanAddRepositoryToTeamRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class TeamPackagesEdit extends Component
{
    use AuthorizesRequests;

    public $package_id;
    public $repository_url;

    public function render()
    {
        return view('livewire.team-packages.edit');
    }

    public function mount($id = false)
    {
        $user = auth()->user();
        $teamId = $user->currentTeam->id;

        $findTeamPackage = TeamPackage::where('id', $id)->with('package')->where('team_id', $teamId)->first();
        if ($findTeamPackage == null) {
            return abort(404, "Package  not found");
        }

        $this->package_id = $findTeamPackage->package->id;
        $this->repository_url = $findTeamPackage->package->repository_url;

    }

    public function edit()
    {




      //  $this->redirect(route('team-packages'));
    }
}
