<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {

            $table->id();
            $table->string('name')->nullable();
            $table->string('repository_url')->nullable();
            $table->longText('package_json')->nullable();

            $table->foreignId('user_id')->nullable()->index();

            $table->text('clone_status')->nullable();
            $table->longText('clone_log')->nullable();
            $table->integer('is_cloned')->default(0)->nullable();

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
        Schema::dropIfExists('packages');
    }
}
