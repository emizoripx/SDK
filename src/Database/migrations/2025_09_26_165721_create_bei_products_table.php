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
        Schema::create('bei_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('bei_account_id')->index();
            $table->string('bei_product_code');
            $table->string("bei_sin_product_code");
            $table->string("bei_activity_code");
            $table->string("bei_unit_code");
            $table->string("bei_unit_name");
            $table->timestamps();
            $table->unique(['bei_account_id','bei_product_code']);
        });
    }

};
