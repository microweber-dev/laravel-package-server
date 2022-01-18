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
    public $period_stats = 'hourly';
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

            if ($this->period_stats == 'monthly') {
                // monthly group
                $groupBy = ['stats_year', 'stats_month'];
            } else if ($this->period_stats == 'daily') {
                // daily group
                $groupBy = ['stats_year', 'stats_month','stats_day'];
            } else {
                // hourly group
                $groupBy = ['stats_year', 'stats_month', 'stats_day', 'stats_hour'];
            }

            //$this->download_stats[] = ['Month', 'Downloads'];
            $this->download_stats[] = ['Hour', 'Downloads'];
          //  $this->download_stats[] = ['Day', 'Downloads'];

            $packageDownloadStats = PackageDownloadStats::where('package_id', $package->id)
               ->groupBy($groupBy)
                ->get();

            if ($packageDownloadStats->count() > 0) {

                $groupedStats = [];
                foreach ($packageDownloadStats as $packageStats) {

                    if ($this->period_stats == 'monthly') {
                        $groupedStatsKey = $packageStats->stats_year . '-' . $packageStats->stats_month;
                    } else if ($this->period_stats == 'daily') {
                        $groupedStatsKey = $packageStats->stats_year . '-' . $packageStats->stats_month . '-' . $packageStats->stats_day;
                    } else {
                        $groupedStatsKey = $packageStats->stats_year . '-' . $packageStats->stats_month . '-' . $packageStats->stats_day . '-' . $packageStats->stats_hour;
                    }

                    if (!isset( $groupedStats[$groupedStatsKey])) {
                        $groupedStats[$groupedStatsKey] = 0;
                    }
                   $groupedStats[$groupedStatsKey]++;
                }

                if (!empty($groupedStats)) {
                    foreach ($groupedStats as $statsDate=>$statsCount) {
                        $this->download_stats[] = [$statsDate, $statsCount];
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
