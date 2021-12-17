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
    public $is_modal_open = 0;

    public function render()
    {
        $userId = auth()->user()->id;
        $packages = Package::where('user_id', $userId)
            ->where('name', 'LIKE', "%$this->keyword%")
            ->where('repository_url', 'LIKE', "%$this->keyword%")
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

        session()->flash('message', $this->id ? 'Package Updated Successfully.' : 'Package Created Successfully.');

        $this->is_modal_open = 0;
        $this->repository_url = '';

    }

    public function update($id)
    {
        $userId = auth()->user()->id;

        $package = Package::where('id', $id)->where('user_id', $userId)->first();

        session()->flash('message', 'Package Updated Successfully.');

    }

    public function delete($id)
    {
        $userId = auth()->user()->id;

        Package::where('id', $id)->where('user_id', $userId)->delete();

        session()->flash('message', 'Package Deleted Successfully.');

    }
}
