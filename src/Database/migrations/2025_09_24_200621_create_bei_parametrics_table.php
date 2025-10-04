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
        // const MOTIVO_ANULACION = "motivos-de-anulacion";
        // const TIPOS_DOCUMENTO_IDENTIDAD = "tipos-documento-de-identidad";
        // const METODOS_DE_PAGO = "metodos-de-pago";
        // const UNIDADES = "unidades";

        Schema::create('bei_global_parametrics', function(Blueprint $table){
            $table->string('bei_code');
            $table->text('bei_description');
            $table->string('bei_type');// PAYMENT_METHODS, ETC

        });
        // const ACTIVIDADES = "actividades";
        // const LEYENDAS = "leyendas";
        // const PRODUCTOS_SIN = "productos-sin";

        Schema::create('bei_specific_parametrics', function (Blueprint $table) {
            $table->string('bei_code');
            $table->text('bei_description');
            $table->string('bei_activity_code')->nullable();
            $table->string('bei_type');
            $table->string('bei_account_id');

            $table->index('bei_code');
            $table->index('bei_type');
            $table->index(['bei_type', 'bei_account_id']);
        });


    }

};
