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

class MyPackagesEdit extends Component
{
    use AuthorizesRequests;

    public $package_id;
    public $repository_url;
    public $team_ids;

    public function render()
    {
        return view('livewire.packages.edit');
    }

    public function mount($id = false)
    {
        $this->package_id = $id;
        $package = Package::where('user_id',auth()->user()->id)->where('id', $this->package_id)->first();
        if ($package == null) {
            return abort(404, "Package  not found");
        }

        $this->repository_url = $package->repository_url;
        $this->team_ids = $package->teams()->pluck('team_id')->toArray();
    }

    public function edit()
    {
        $validation = [];
        $validation['team_ids'] = ['required','array', new CanAddRepositoryToTeamRule()];

        if (empty($this->package_id)) {
            $validation['repository_url'] = ['required', 'url', 'unique:packages'];
        }

        $this->validate($validation);

        $userId = auth()->user()->id;

        $newPackageAdd = false;
        $package = Package::where('user_id',$userId)->where('id', $this->package_id)->first();
        if ($package == null) {

            $package = new Package();
            $package->user_id = $userId;
            $package->clone_status = Package::CLONE_STATUS_WAITING;
            $package->repository_url = $this->repository_url;
            $package->save();

            $newPackageAdd = true;
        }

        if (!empty($this->team_ids)) {
            $package->teams()->sync($this->team_ids);
        }

        if ($newPackageAdd) {
            dispatch(new ProcessPackageSatis($package->id));
        }

        $this->redirect(route('my-packages'));
    }
}
