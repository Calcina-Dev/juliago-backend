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
    {
        Schema::create('recetas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->constrained('empresas');
    $table->foreignId('producto_id')->constrained('productos');
    $table->foreignId('insumo_id')->constrained('insumos');
    $table->decimal('cantidad', 10, 2);
    $table->timestamps();
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
