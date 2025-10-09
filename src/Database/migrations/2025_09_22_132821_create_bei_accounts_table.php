<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bei_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('bei_enable')->default(true);
            $table->boolean('bei_verified_setup')->default(false);
            $table->string('bei_client_id')->unique();
            $table->text('bei_client_secret');
            $table->text('bei_token')->nullable();
            $table->timestamp('bei_deadline_token')->nullable();
            $table->string('bei_host');
            $table->json('bei_branches')->nullable();
            $table->boolean('bei_demo')->default(false);
            $table->string('owner_id')->nullable();
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
        Schema::dropIfExists('bei_accounts');
    }

};
