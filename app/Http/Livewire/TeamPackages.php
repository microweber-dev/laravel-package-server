<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\TeamPackage;
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

    public function render()
    {
        $keyword = $this->keyword;

        $user = auth()->user();
        $userId = $user->id;
        $teamId = $user->currentTeam->id;
        $this->team = $user->currentTeam;

        $teamPackages = TeamPackage::where('team_id', $teamId)
            ->whereHas('package')
            ->whereHas('team')
            ->with('package')
            ->with('team')
           ->paginate(15);

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
