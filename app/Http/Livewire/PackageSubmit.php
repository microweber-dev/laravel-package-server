<?php

namespace App\Http\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PackageSubmit extends Component
{
    use AuthorizesRequests;

    public $name;

    protected $rules = [
        'name' => 'required|min:6',
    ];

    public function render()
    {
        return view('livewire.package-submit', [
            'name' => $this->name,
        ]);
    }

    public function submitPackage()
    {
        $this->validate();


        dump($this->name);

    }
}
