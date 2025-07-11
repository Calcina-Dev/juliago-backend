<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_add_activa_to_empresas_table.php
    public function up()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->boolean('activa')->default(true)->after('modo_mantenimiento');
        });
    }

    public function down()
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn('activa');
        });
    }

};
