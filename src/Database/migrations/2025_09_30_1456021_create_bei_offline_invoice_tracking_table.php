<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('bei_offline_invoice_tracking', function(Blueprint $table){
            $table->id();
            $table->string('ticket');
            $table->dateTime('registered_at');
            $table->unsignedInteger('tries')->default(0);
            $table->dateTime('last_tried_at')->nullable();
            $table->dateTime('next_try')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bei_request_logs');
    }
};
