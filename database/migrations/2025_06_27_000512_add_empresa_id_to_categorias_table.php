<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {Schema::table('categorias', function (Blueprint $table) {
    $table->foreignId('empresa_id')
        ->after('id')
        ->constrained('empresas')
        ->cascadeOnDelete();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            //
        });
    }
};
