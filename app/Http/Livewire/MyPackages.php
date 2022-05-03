<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class MyPackages extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $keyword = '';
    public $checkBackgroundJob = false;
    public $confirmingDeleteId = false;

    public function render()
    {
        $keyword = $this->keyword;

        if (request()->get('check_for_background_job') == 1) {
            $this->checkBackgroundJob = true;
        }

        $packages = Package::when(!empty($keyword), function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
                $q->orWhere('repository_url', 'LIKE', "%$keyword%");
            })
            ->userHasAccess()
            ->orderBy('id','DESC')
           ->paginate(100);

        return view('livewire.packages.index', compact('packages'));

    }

    public function update($id)
    {
        $package = Package::where('id', $id)->userHasAccess()->first();
        $package->clone_status = Package::CLONE_STATUS_WAITING;
        $package->save();

        dispatch(new ProcessPackageSatis($package->id));

        $this->checkBackgroundJob = true;

    }

    public function updateAllPacakges()
    {
        $packages = Package::select(['id','user_id'])->userHasAccess()->get();
        if ($packages->count() > 0) {
            foreach ($packages as $package) {
                $package->clone_status = Package::CLONE_STATUS_WAITING;
                $package->save();
                dispatch(new ProcessPackageSatis($package->id));
            }
            $this->checkBackgroundJob = true;
        }

    }

    public function confirmDelete($id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete($id)
    {
        $package = Package::where('id',$id)->userHasAccess()->with('teams')->first();
        if ($package == null) {
            return [];
        }

        if (!empty($package->teams)) {
            $package->teams()->detach();
        }

        $package->delete();

        session()->flash('message', 'Package deleted successfully.');

    }

    public function backgroundJobStatus()
    {
        $findRunningPackages = Package::userHasAccess()
            ->where('clone_status', Package::CLONE_STATUS_RUNNING)
            ->count();

        if ($findRunningPackages > 0) {
            $this->checkBackgroundJob = true;
        } else {
            $this->checkBackgroundJob = false;
        }
    }
}
