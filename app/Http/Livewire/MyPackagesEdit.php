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
    public $package_user_id;
    public $repository_url;
    public $team_ids = [];
    public $credentials = [];
    public $credential_id;
    public $team_owner_id;

    public function render()
    {
        if (request()->get('repository_url')) {
            $this->repository_url = request()->get('repository_url');
        }

        if (request()->get('team_id')) {
            $this->team_ids[] = request()->get('team_id');
            $this->team_owner_id = request()->get('team_id');
        }

        return view('livewire.packages.edit');
    }

    public function mount($id = false)
    {
        $user = auth()->user();

        $this->package_id = $id;

        if ($this->package_id) {
            $package = Package::where('user_id', $user->id)->where('id', $this->package_id)->first();
            if ($package == null) {
                return abort(404, "Package  not found");
            }

            $this->team_owner_id = $package->team_owner_id;
            $this->package_user_id = $package->user_id;
            $this->repository_url = $package->repository_url;
            $this->credential_id = $package->credential_id;

            $this->team_ids = $package->teams()->pluck('team_id')->toArray();
        }

        $this->credentials = $user->credentials()->get();
    }

    public function edit()
    {
        $validation = [];
        $validation['team_ids'] = ['required', 'array', new CanAddRepositoryToTeamRule()];

        if (empty($this->package_id)) {
            $validation['repository_url'] = ['required', 'url', 'unique:packages'];
        }

        $this->validate($validation);

        $user = auth()->user();

        $newPackageAdd = false;
        $package = Package::where('user_id', $user->id)->where('id', $this->package_id)->first();
        if ($package == null) {

            $package = new Package();
            $package->user_id = $user->id;
            $package->clone_status = Package::CLONE_STATUS_WAITING;
            $package->repository_url = $this->repository_url;

            $newPackageAdd = true;
        }

        $package->team_owner_id = $this->team_owner_id;
        $package->credential_id = $this->credential_id;
        $package->save();

        if (!empty($this->team_ids)) {
            $package->teams()->sync($this->team_ids);
        }

        if ($newPackageAdd) {
            dispatch(new ProcessPackageSatis($package->id));
        }

        $this->redirect(route('my-packages').'?check_for_background_job=1');
    }
}
