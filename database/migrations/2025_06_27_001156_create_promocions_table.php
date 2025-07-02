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
        Schema::create('promociones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->constrained('empresas');
    $table->string('nombre');
    $table->string('tipo'); // combo, porcentaje, fijo, etc.
    $table->json('reglas')->nullable(); // por categoría, día, hora
    $table->boolean('activo')->default(true);
    $table->timestamps();
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocions');
    }
};
