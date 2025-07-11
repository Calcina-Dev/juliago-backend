<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeEmpresaIdNullableInUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Necesario para cambiar campos existentes en PostgreSQL
            $table->unsignedBigInteger('empresa_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revertir a NOT NULL si fuera necesario
            $table->unsignedBigInteger('empresa_id')->nullable(false)->change();
        });
    }
}
