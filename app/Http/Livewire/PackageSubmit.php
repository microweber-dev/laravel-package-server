<?php

namespace App\Http\Livewire;

use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PackageSubmit extends Component
{
    use AuthorizesRequests;

    public $repositoryUrl;

    protected $rules = [
        'repository_url' => 'required|url',
    ];

    public function render()
    {
        return view('livewire.package-submit', [
            'repository_url' => $this->repositoryUrl,
        ]);
    }

    public function submitPackage()
    {
        $this->validate();

        $createPackage = new Package();
        $createPackage->repository_url = $this->repositoryUrl;
        $createPackage->save();

        return $this->redirect(route('dashboard'));

    }
}
