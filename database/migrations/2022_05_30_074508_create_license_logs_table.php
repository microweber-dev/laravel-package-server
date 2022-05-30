<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->string('license_id');
            $table->string('whmcs_service_id');
            $table->string('whmcs_license_id');
            $table->string('domain');
            $table->string('ip');
            $table->string('last_access');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('license_logs');
    }
}
