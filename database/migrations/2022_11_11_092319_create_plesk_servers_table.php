<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePleskServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plesk_servers', function (Blueprint $table) {

            $table->id();

            $table->text('server_ip')->nullable();
            $table->dateTime('first_access_date')->nullable();
            $table->dateTime('last_access_date')->nullable();
            $table->integer('access_count')->nullable();

            $table->text('name')->nullable();
            $table->text('number')->nullable();
            $table->integer('active')->nullable();
            $table->text('app')->nullable();
            $table->text('key_number')->nullable();
            $table->text('key_version')->nullable();
            $table->text('product')->nullable();
            $table->text('start_date')->nullable();
            $table->text('lim_date')->nullable();
            $table->text('license_server_url')->nullable();
            $table->text('license_update_date')->nullable();
            $table->text('update_ticket')->nullable();
            $table->longText('key_body')->nullable();
            $table->longText('extension_info')->nullable();
            $table->longText('properties_config')->nullable();

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
        Schema::dropIfExists('plesk_servers');
    }
}
