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
    public $period_stats = 'monthly';
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

            $this->download_stats = [];

            $this->period_stats = 'monthly';

            if ($this->period_stats == 'monthly') {
                // monthly group
                $this->download_stats[] = ['Month', 'Downloads'];
                $packageDownloadStats = PackageDownloadStats::select(
                    DB::raw("month(created_at) as stats_date")ls,
                    DB::raw("SUM(id) as total_ids"))
                    ->where('package_id', $package->id)
                    ->orderBy(DB::raw("month(created_at)"))
                    ->groupBy(DB::raw("month(created_at)"))
                    ->get();
            } else if ($this->period_stats == 'daily') {
                // daily group
                $this->download_stats[] = ['Day', 'Downloads'];
                $packageDownloadStats = PackageDownloadStats::select(
                    DB::raw("day(created_at) as stats_date"),
                    DB::raw("SUM(id) as total_ids"))
                    ->where('package_id', $package->id)
                    ->orderBy(DB::raw("day(created_at)"))
                    ->groupBy(DB::raw("day(created_at)"))
                    ->get();
            } else {
                // hourly group
                $this->download_stats[] = ['Hour', 'Downloads'];
                $packageDownloadStats = PackageDownloadStats::select(
                    DB::raw("hour(created_at) as stats_date"),
                    DB::raw("SUM(id) as total_ids"))
                    ->where('package_id', $package->id)
                    ->orderBy(DB::raw("hour(created_at)"))
                    ->groupBy([DB::raw("hour(created_at)")])
                    ->get();
            }

            if ($packageDownloadStats->count() > 0) {
                foreach ($packageDownloadStats as $packageStatsKey => $packageStats) {
                    $this->download_stats[++$packageStatsKey] = [$packageStats->stats_date, (int)$packageStats->total_ids];
                }
            }

            $this->repository_url = $package->repository_url;
            $this->credential_id = $package->credential_id;
            $this->team_ids = $package->teams()->pluck('team_id')->toArray();
        }

        $this->credentials = $user->credentials()->get();
    }

}
