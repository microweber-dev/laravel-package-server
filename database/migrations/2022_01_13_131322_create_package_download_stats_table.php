<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageDownloadStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_download_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id');
            $table->string('name');
            $table->string('version');
            $table->longText('authorization');
            $table->string('host');
            $table->string('user_agent');
            $table->string('ip_address');
            $table->integer('stats_hour');
            $table->integer('stats_day');
            $table->integer('stats_month');
            $table->integer('stats_year');
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
        Schema::dropIfExists('package_download_stats');
    }
}
