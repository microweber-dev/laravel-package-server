<?php

namespace App\Jobs;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPackageSubmit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $packageModel;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->packageModel = $package;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        

        dump($this->packageModel->repository_url);

    }
}
