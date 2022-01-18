<?php

namespace App\Http\Livewire;

use App\Jobs\ProcessPackageSubmit;
use App\MicroweberChart;
use App\Models\Package;
use App\Models\PackageDownloadStats;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MyPackagesShow extends Component
{
    use AuthorizesRequests;

    public $package_id;
    public $repository_url;
    public $team_ids = [];
    public $credentials = [];
    public $period_stats = 'monthly';
    public $chart_html = '';
    public $download_stats = false;
    public $credential_id;

    public function render()
    {
        $user = auth()->user();

        if ($this->package_id) {

            $package = Package::where('id', $this->package_id)->first();
            if ($package == null) {
                return abort(404, "Package  not found");
            }

            $this->download_stats = [];

            if ($this->period_stats == 'monthly') {
                $chartOptions = [
                    'chart_name'=>'download_statistic',
                    'chart_title' => 'Downloads by month',
                    'report_type' => 'group_by_date',
                    'model' => PackageDownloadStats::class,
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'month',
                    'chart_type' => 'bar',
                    'where_raw'=> 'package_id = ' . $package->id
                ];
            } else if ($this->period_stats == 'daily') {
                $chartOptions = [
                    'chart_name'=>'download_statistic',
                    'chart_title' => 'Downloads by day',
                    'report_type' => 'group_by_date',
                    'model' => PackageDownloadStats::class,
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'day',
                    'chart_type' => 'bar',
                    'where_raw'=> 'package_id = ' . $package->id
                ];
            } else {
                $chartOptions = [
                    'chart_name'=>'download_statistic',
                    'chart_title' => 'Downloads by hour',
                    'report_type' => 'group_by_date',
                    'model' => PackageDownloadStats::class,
                    'group_by_field' => 'created_at',
                    'group_by_period' => 'hour',
                    'chart_type' => 'bar',
                    'where_raw'=> 'package_id = ' . $package->id
                ];
            }

            $chart = new MicroweberChart($chartOptions);
            $this->chart_js = $chart->getDataSets();

            $this->repository_url = $package->repository_url;
            $this->credential_id = $package->credential_id;
            $this->team_ids = $package->teams()->pluck('team_id')->toArray();
        }

        $this->credentials = $user->credentials()->get();

        return view('livewire.packages.show');
    }

    public function mount($id = false)
    {
        $this->package_id = $id;
    }


}
