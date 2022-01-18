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
    public $download_stats = false;
    public $credential_id;

    public function render()
    {
        $this->updateChart();

        return view('livewire.packages.show');
    }

    public function mount($id = false)
    {
        $this->package_id = $id;
    }


    public function updateChart()
    {
        $user = auth()->user();
        $this->credentials = $user->credentials()->get();

        if ($this->package_id) {

            $package = Package::where('id', $this->package_id)->first();
            if ($package == null) {
                return abort(404, "Package  not found");
            }


            $this->description = $package->description;
            $this->screenshot = $package->screenshot();
            $this->readme = $package->readme();

            $this->download_stats = [];

            $chartOptions = [
                'chart_name'=>'download_statistic',
                'chart_title' => 'Hourly downloads',
                'report_type' => 'group_by_date',
                'model' => PackageDownloadStats::class,
                'group_by_field' => 'created_at',
                'group_by_period' => 'hour',
                'chart_type' => 'bar',
                'where_raw'=> 'package_id = ' . $package->id
            ];

            if ($this->period_stats == 'monthly') {
                $chartOptions['chart_title'] = 'Monthly downloads';
                $chartOptions['group_by_period'] = 'month';
            } else if ($this->period_stats == 'yearly') {
                $chartOptions['chart_title'] = 'Yearly downloads';
                $chartOptions['group_by_period'] = 'year';
            } else if ($this->period_stats == 'daily') {
                $chartOptions['chart_title'] = 'Daily downloads';
                $chartOptions['group_by_period'] = 'day';
            }

            $chart = new MicroweberChart($chartOptions);
            $this->charts_data = $chart->getDataSets();

        ///    $this->emit('chartsData', $this->charts_data);

            $this->repository_url = $package->repository_url;
            $this->credential_id = $package->credential_id;
            $this->team_ids = $package->teams()->pluck('team_id')->toArray();
        }
    }

}
