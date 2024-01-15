<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSubmit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MyPackagesBulkAdd extends Component
{
    use AuthorizesRequests;

    public function render()
    {
        return view('livewire.packages.bulk-add');
    }
}
