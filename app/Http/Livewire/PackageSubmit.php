<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PackageSubmit extends Component
{
    use AuthorizesRequests;

    public $repositoryUrl;

    protected $rules = [
        'repositoryUrl' => 'required|url',
    ];

    public function render()
    {
        return view('livewire.package-submit', [
            'repositoryUrl' => $this->repositoryUrl,
        ]);
    }

    public function submitPackage()
    {
        $this->validate();

        $createPackage = new Package();
        $createPackage->repository_url = $this->repositoryUrl;
        $createPackage->save();

        dispatch(new ProcessPackageSubmit($createPackage));

        return $this->redirect(route('dashboard'));

    }
}
