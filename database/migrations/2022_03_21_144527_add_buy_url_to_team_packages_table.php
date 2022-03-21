<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBuyUrlToTeamPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_packages', function (Blueprint $table) {
            $table->integer('buy_url')->nullable()->default(0);
            $table->integer('buy_url_from')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_packages', function (Blueprint $table) {
            //
        });
    }
}
