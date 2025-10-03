<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bei_invoices', function (Blueprint $table) {

            /**
             * Automatic / Core data
             */
            $table->id()->primary();
            $table->string('bei_ticket')->unique();
            $table->string('bei_account_id');
            $table->enum('bei_revocation_code',[1,2,3,4])->nullable();

            $table->enum('bei_step_emission', ['none', 'sent', 'in_progress', 'complete'])->default('none');
            $table->enum('bei_step_revocation', ['none', 'sent', 'in_progress', 'complete'])->default('none');
            $table->decimal('bei_amount_total', 10, 2)->default(0);
            $table->unsignedInteger('bei_sector_document_id')->nullable();
            $table->unsignedInteger('bei_pos_code')->nullable();
            $table->unsignedInteger('bei_branch_code')->nullable();
            $table->unsignedInteger('bei_payment_method')->nullable();

            /**
             * Optional / Extensible data
             */
            $table->json('bei_client')->nullable();     // cliente flexible
            $table->json('bei_details')->nullable();    // lineas de factura
            $table->json('bei_additional')->nullable(); // extras (leyendas, address, etc.)

            /**
             * Response / Emission results
             */
            $table->dateTime('bei_emission_date')->nullable();
            $table->dateTime('bei_revocation_date')->nullable();
            $table->string('bei_cuf')->nullable()->unique();
            $table->boolean('bei_online')->default(1);
            $table->string('bei_pdf_url', 255)->nullable();

            /**
             * Exception / gift card
             */
            $table->decimal('bei_giftcard_amount', 10, 2)->default(0);
            $table->string('bei_exception_code')->default('0');

            /**
             * Timestamps
             */
            $table->timestamps();

            /**
             * Indexes
             */
            $table->index(['bei_account_id', 'bei_ticket']);
            $table->index('bei_branch_code');
            $table->index('bei_pos_code');
            $table->index('bei_payment_method');
            $table->index('bei_emission_date');
        });

        // Fulltext index opcional para búsquedas de cliente
//        DB::statement('ALTER TABLE bei_invoices ADD FULLTEXT search(bei_client)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bei_invoices');
    }
};
