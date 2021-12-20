<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\Team;
use App\Rules\CanAddRepositoryToTeamRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;

class MyPackagesCreate extends Component
{
    use AuthorizesRequests;

    public $repository_url;
    public $team_ids;

    public function render()
    {
        return view('livewire.packages.create');
    }

    public function store()
    {
        $this->validate([
            'team_ids' => ['required','array', new CanAddRepositoryToTeamRule()],
            'repository_url' => 'required|url|unique:packages',
        ]);

        $userId = auth()->user()->id;

        $package = new Package();
        $package->user_id = $userId;
        $package->clone_status = Package::CLONE_STATUS_WAITING;
        $package->repository_url = $this->repository_url;
        $package->save();

        foreach ($this->team_ids as $teamId=>$teamVal) {
            $findTeam = Team::where('id', $teamId)->first();
            if ($findTeam !== null) {
                $package->teams()->attach($findTeam->id);
            }
        }

        dispatch(new ProcessPackageSatis($package->id));

        $this->redirect(route('my-packages'));
    }
}
