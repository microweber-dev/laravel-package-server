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

            $table->string('preview_url')->nullable();
            $table->string('version')->nullable();
            $table->string('icon')->nullable();
            $table->string('screenshot')->nullable();
            $table->string('description')->nullable();
            $table->string('readme')->nullable();
            $table->string('homepage')->nullable();
            $table->string('type')->nullable();
            $table->string('target_dir')->nullable();
            $table->string('last_version')->nullable();

            $table->longText('keywords')->nullable();

            $table->longText('package_json')->nullable();

            $table->float('last_version_filesize')->nullable();
            $table->float('all_versions_filesize')->nullable();

            $table->foreignId('user_id')->nullable()->index();
            $table->foreignId('team_owner_id')->nullable()->index();

            $table->text('clone_status')->nullable();
            $table->timestamp('clone_queue_at')->nullable();
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
