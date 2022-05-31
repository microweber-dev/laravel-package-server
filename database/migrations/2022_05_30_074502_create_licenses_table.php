<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license');
            $table->foreignId('whmcs_server_id');
            $table->string('whmcs_service_id');
            $table->string('whmcs_license_id');
            $table->string('whmcs_valid_domain');
            $table->string('whmcs_valid_ip');
            $table->string('whmcs_last_access');
            $table->string('whmcs_allow_reissues');
            $table->string('whmcs_allow_domain_conflicts');
            $table->string('whmcs_allow_ip_conflicts');
            $table->string('whmcs_status');
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
        Schema::dropIfExists('licenses');
    }
}
