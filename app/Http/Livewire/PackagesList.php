<?php

namespace App\Http\Livewire;

use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;

class PackagesList extends Component
{
    use WithPagination;

    public function render()

    {
        $userId = auth()->user()->id;
        $packages = Package::where('user_id', $userId)->paginate(15);

        return view('livewire.packages', [
            'packages' => $packages,
        ]);

    }

}
