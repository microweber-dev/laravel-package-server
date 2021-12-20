<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class MyPackagesCreate extends Component
{
    use AuthorizesRequests;

    public $repository_url;

    public function render()
    {

        return view('livewire.packages.create');

    }

    public function store()
    {
        $this->validate([
            'repository_url' => 'required|url|unique:packages',
        ]);

        $userId = auth()->user()->id;

        $package = new Package();
        $package->user_id = $userId;
        $package->clone_status = Package::CLONE_STATUS_WAITING;
        $package->repository_url = $this->repository_url;
        $package->save();

        dispatch(new ProcessPackageSatis($package->id));

    }
}
