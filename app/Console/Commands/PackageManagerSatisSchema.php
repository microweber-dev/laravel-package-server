<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PackageManagerSatisSchema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-manager:change-satis-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change satis chema.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Ovewrite satis chema json
        $overwriteSatisSchemaFile = base_path() . '/vendor/composer/satis/res/satis-schema.json';
        $satisSchemaFile = base_path() . '/satis-schema.json';


        $satisSchemaContent = file_get_contents($satisSchemaFile);
        if ($satisSchemaContent) {
            file_put_contents($overwriteSatisSchemaFile, $satisSchemaContent);
        }

        // Overwrite index twig
        $overwriteTwigFile = base_path() . '/vendor/composer/satis/views/index.html.twig';
        $twigFile = base_path() . '/resources/views/satis_layouts/index.html.twig';

        $twigFileContent = file_get_contents($twigFile);
        if ($twigFileContent) {
            file_put_contents($overwriteTwigFile, $twigFileContent);
        }

        // Overwrite package twig
        $overwriteTwigFile = base_path() . '/vendor/composer/satis/views/package.html.twig';
        $twigFile = base_path() . '/resources/views/satis_layouts/package.html.twig';

        $twigFileContent = file_get_contents($twigFile);
        if ($twigFileContent) {
            file_put_contents($overwriteTwigFile, $twigFileContent);
        }
        
    }
}
