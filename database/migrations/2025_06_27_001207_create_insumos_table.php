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
        Schema::create('insumos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->constrained('empresas');
    $table->string('nombre');
    $table->string('unidad'); // ej: kg, ml, und
    $table->decimal('stock', 10, 2)->default(0);
    $table->timestamps();
    $table->softDeletes();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
