<?php

namespace Tests\Unit;

use App\Helpers\SatisHelper;
use PHPUnit\Framework\TestCase;

class SatisHelperTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {

        $packages = [

        ];
        $latestVersion = SatisHelper::getLatestVersionFromPackage($packages);

    }
}
