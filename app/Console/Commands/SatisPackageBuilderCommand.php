<?php

namespace App\Console\Commands;

use App\SatisPackageBuilder;
use Illuminate\Console\Command;

class SatisPackageBuilderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package-builder:build-with-satis {--file=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build repository package with saits';

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
     * @return int
     */
    public function handle()
    {
        $file = $this->option('file')[0];


        SatisPackageBuilder::build($file);

        return 0;
    }
}
