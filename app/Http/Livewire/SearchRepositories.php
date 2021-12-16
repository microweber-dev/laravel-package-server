<?php

namespace App\Http\Livewire;

use Livewire\Component;

class SearchRepositories extends Component
{
    public $keyword = '';

    public function render()

    {
        return view('livewire.search-repositories', [
            'keyword' => $this->keyword,
        ]);

    }

}
