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
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
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
    public $chart_html = '';
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

            $chartOptions = [
                'chart_title' => 'Downloads',
                'report_type' => 'group_by_date',
                'model' => PackageDownloadStats::class,
                'group_by_field' => 'created_at',
                'group_by_period' => 'day',
                'chart_type' => 'bar',
                'where_raw'=> 'package_id = ' . $package->id
            ];
            $chart = new LaravelChart($chartOptions);
            $this->chart_html = $chart->renderHtml()->render();
            $this->chart_js_library = $chart->renderChartJsLibrary();
            $this->chart_js = $chart->renderJs()->render();

            $this->repository_url = $package->repository_url;
            $this->credential_id = $package->credential_id;
            $this->team_ids = $package->teams()->pluck('team_id')->toArray();
        }

        $this->credentials = $user->credentials()->get();
    }

}
