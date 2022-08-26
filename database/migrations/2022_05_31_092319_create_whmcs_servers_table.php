<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhmcsServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whmcs_servers', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('ip')->nullable();
            $table->text('url');
            $table->text('api_auth_type')->nullable();
            $table->text('api_username')->nullable();
            $table->text('api_password')->nullable();
            $table->text('api_identifier')->nullable();
            $table->text('api_secret')->nullable();
            $table->foreignId('team_id');
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
        Schema::dropIfExists('whmcs_servers');
    }
}
