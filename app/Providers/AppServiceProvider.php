<?php

namespace App\Providers;

use App\Http\Livewire\TeamPackagesTable;
use DarthSoup\Whmcs\WhmcsServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(BladeCompiler::class, function () {
            Livewire::component('team-packages-table', TeamPackagesTable::class);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
