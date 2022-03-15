<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToTeamPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_packages', function (Blueprint $table) {
            $table->text('whmcs_product_ids')->nullable();
            $table->integer('is_visible')->nullable()->default(1);
            $table->integer('is_paid')->nullable()->default(0);
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
