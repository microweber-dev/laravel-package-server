<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class Packages extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $keyword = '';
    public $repository_url;
    public $is_modal_open = false;
    public $check_background_job = true;

    public function render()
    {
        $keyword = $this->keyword;

        $userId = auth()->user()->id;
        $packages = Package::where('user_id', $userId)
            ->where('clone_status', Package::CLONE_STATUS_SUCCESS)
            ->when(!empty($keyword), function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
                $q->where('repository_url', 'LIKE', "%$keyword%");
            })
           ->paginate(15);

        return view('livewire.packages.index', compact('packages'));

    }

    public function create()
    {
        $this->repository_url = '';
        $this->is_modal_open = 1;
    }

    public function store()
    {
        $this->validate([
            'repository_url' => 'required|url|unique:packages',
        ]);

        $userId = auth()->user()->id;

        $package = new Package();
        $package->user_id = $userId;
        $package->repository_url = $this->repository_url;
        $package->save();

        dispatch(new ProcessPackageSubmit($package->id));

        $this->is_modal_open = 0;
        $this->repository_url = '';

    }

    public function update($id)
    {
        $userId = auth()->user()->id;

        $package = Package::where('id', $id)->where('user_id', $userId)->first();

    }

    public function delete($id)
    {
        $userId = auth()->user()->id;

        Package::where('id', $id)->where('user_id', $userId)->delete();

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
