<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MyPackagesBulkAdd extends Component
{
    use AuthorizesRequests;

    public $repository_urls;
    public $team_ids = [];
    public $credentials = [];
    public $credential_id;
    public $team_owner_id;

    public function mount()
    {
        $user = auth()->user();
        $this->credentials = $user->credentials()->get();
    }

    public function save()
    {
        $this->validate([
            'repository_urls' => 'required',
            'team_ids' => 'required',
            'credential_id' => 'required',
        ]);

        $repository_urls = explode("\n", $this->repository_urls);
        $repository_urls = array_map('trim', $repository_urls);
        $repository_urls = array_filter($repository_urls);
        if (count($repository_urls) == 0) {
            return $this->addError('repository_urls', 'Please enter at least one repository URL');
        }

        $user = auth()->user();

        foreach ($repository_urls as $repository_url) {

            $newPackageAdd = false;
            $package = Package::where('repository_url', $repository_url)->userHasAccess()->first();
            if ($package == null) {

                $package = new Package();
                $package->user_id = $user->id;
                $package->clone_status = Package::CLONE_STATUS_WAITING;
                $package->repository_url = $repository_url;

                $newPackageAdd = true;
            }

            $package->team_owner_id = $this->team_owner_id;
            $package->credential_id = $this->credential_id;

            $package->save();

            if (!empty($this->team_ids)) {
                $package->teams()->sync($this->team_ids);
            }

            if ($newPackageAdd) {
                $package->updatePackageWithSatis();
            }
        }

        $this->redirect(route('my-packages').'?check_for_background_job=1');
    }

    public function render()
    {
        return view('livewire.packages.bulk-add');
    }
}
