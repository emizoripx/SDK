<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::table('bei_accounts', function (Blueprint $table) {
            $table->string('owner_type')->nullable()->after('owner_id');
            $table->index('owner_id');
            $table->unique(['owner_type', 'owner_id']);
        });
    }

    public function down()
    {
        Schema::table('bei_accounts', function (Blueprint $table) {
            $table->dropUnique(['owner_type', 'owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_type');
        });
    }
};