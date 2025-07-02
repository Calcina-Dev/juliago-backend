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
   Schema::create('cierres_caja', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->constrained('empresas');
    $table->foreignId('usuario_id')->constrained('users');
    $table->decimal('monto_total', 10, 2)->default(0);
    $table->timestamp('inicio_turno');
    $table->timestamp('fin_turno')->nullable();
    $table->string('estado')->default('abierto'); // cerrado, anulado, etc.
    $table->timestamps();
    $table->softDeletes(); // ✅
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierre_cajas');
    }
};
