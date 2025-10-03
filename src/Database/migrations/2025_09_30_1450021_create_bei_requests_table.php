<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bei_request_logs', function(Blueprint $table){
            $table->uuid('id');
            $table->string('bei_ticket');
            $table->string('bei_event'); // EMISSION_EVENT, REJECTION_EVENT, GET_DETAIL_EVENT
            $table->string('bei_send_request_date');
            $table->string('bei_receive_response_date')->nullable();
            $table->json('bei_request');
            $table->json('bei_response');
            $table->string('bei_http_code');
            $table->index('bei_ticket');
            $table->index(['bei_ticket','bei_event']);
            $table->index('bei_send_request_date');
            $table->index(['bei_event','bei_send_request_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bei_request_logs');
    }
};
