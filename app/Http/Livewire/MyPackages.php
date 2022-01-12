<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class MyPackages extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $keyword = '';
    public $check_background_job = false;
    public $shared_packages = false;

    public function render()
    {
        $keyword = $this->keyword;

        if (request()->get('check_for_background_job') == 1) {
            $this->check_background_job = true;
        }

        $user = auth()->user();
        $teams = $user->allTeams();

        $userAdminInTeams = [];
        foreach ($teams as $team) {
            if ($user->hasTeamRole($team, 'admin')) {
                $userAdminInTeams[] = $team->id;
            }
        }

        $packages = Package::when((!empty($userAdminInTeams) && $this->shared_packages), function ($q) use ($userAdminInTeams) {
                $q->whereIn('team_owner_id', $userAdminInTeams);
            })
            ->when(!empty($keyword), function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
                $q->orWhere('repository_url', 'LIKE', "%$keyword%");
            })
            ->orderBy('id','DESC')
           ->paginate(100);

        return view('livewire.packages.index', compact('packages'));

    }

    public function update($id)
    {
        $userId = auth()->user()->id;

        $package = Package::where('id', $id)->where('user_id', $userId)->first();

        dispatch(new ProcessPackageSatis($package->id));

        $this->check_background_job = true;

    }

    public function delete($id)
    {
        $userId = auth()->user()->id;


        $findPackage = Package::where('id', $id)->where('user_id', $userId)->first();
        $findPackage->teams()->detach();
        $findPackage->delete();

        session()->flash('message', 'Package deleted successfully.');

    }

    public function backgroundJobStatus()
    {
        $userId = auth()->user()->id;

        $findRunningPackages = Package::where('user_id', $userId)
            ->where('clone_status', Package::CLONE_STATUS_RUNNING)
            ->count();

        if ($findRunningPackages > 0) {
            $this->check_background_job = true;
        } else {
            $this->check_background_job = false;
        }
    }
}
