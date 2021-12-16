<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PackageSubmit extends Component
{
    use AuthorizesRequests;

    public $repository_url;

    protected $rules = [
        'repository_url' => 'required|url|unique:packages',
    ];

    public function render()
    {
        return view('livewire.package-submit', [
            'repository_url' => $this->repository_url,
        ]);
    }

    public function submitPackage()
    {
     //   $this->validate();

        $createPackage = new Package();
        $createPackage->user_id = auth()->user()->id;
        $createPackage->repository_url = $this->repository_url;
        $createPackage->save();

        dispatch(new ProcessPackageSubmit($createPackage->id));

     //   return $this->redirect(route('dashboard'));

    }
}
