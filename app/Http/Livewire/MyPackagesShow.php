<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSatis;
use App\Jobs\ProcessPackageSubmit;
use App\Models\Package;
use App\Models\PackageDownloadStats;
use App\Models\Team;
use App\Rules\CanAddRepositoryToTeamRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MyPackagesShow extends Component
{
    use AuthorizesRequests;

    public $package_id;
    public $repository_url;
    public $team_ids = [];
    public $credentials = [];
    public $period_stats = 'daily';
    public $download_stats = false;
    public $credential_id;

    public function render()
    {
        return view('livewire.packages.show');
    }

    public function mount($id = false)
    {
        $user = auth()->user();

        $this->package_id = $id;

        if ($this->package_id) {
            $package = Package::where('id', $this->package_id)->first();
            if ($package == null) {
                return abort(404, "Package  not found");
            }


            if ($this->period_stats = 'daily') {

                $this->download_stats = [];
                $this->download_stats[] = ['Day', 'Downloads'];

                $packageDownloadStats = PackageDownloadStats::where('package_id', $package->id)
                   ->groupBy(['stats_year', 'stats_month','stats_day'])
                    ->get();

                if ($packageDownloadStats->count() > 0) {
                    foreach ($packageDownloadStats as $packageStats) {
                        $this->download_stats[] = ['All', 2];
                    }
                }
            }

            $this->repository_url = $package->repository_url;
            $this->credential_id = $package->credential_id;
            $this->team_ids = $package->teams()->pluck('team_id')->toArray();
        }

        $this->credentials = $user->credentials()->get();
    }

}
