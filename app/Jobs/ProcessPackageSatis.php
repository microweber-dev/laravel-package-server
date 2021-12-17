<?php

namespace App\Jobs;

use App\Models\Package;
use App\RepositoryPathHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symplify\GitWrapper\GitWrapper;

class ProcessPackageSatis implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $packageId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($packageId)
    {
        $this->packageId = $packageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $packageModel = Package::where('id', $this->packageId)->first();

        $satisContent = [
            'name'=>'microweber/packages',
            'homepage'=>'https://github.com/',
            'repositories'=>[
                [
                    'type'=>'vcs',
                    'url'=>RepositoryPathHelper::getRepositoriesClonePath($packageModel->id),
                ]
            ],
            'require-all'=> true,
           /* 'require'=> [
               $packageModel->name =>'dev-master',
            ]*/
             "archive" => [
                "directory"=> "dist",
                "format"=> "zip",
                "skip-dev"=> true
            ],
        ];

        $satisJson = json_encode($satisContent, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        $saitsRepositoryPath = RepositoryPathHelper::getRepositoriesSatisPath($packageModel->id);

        file_put_contents($saitsRepositoryPath . 'satis.json', $satisJson);





    }
}
