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
        Schema::create('pagos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
        $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade'); // cajero
        $table->decimal('monto', 10, 2);
        $table->string('metodo_pago'); // efectivo, tarjeta, etc.
        $table->timestamp('pagado_en')->default(now());
        $table->softDeletes(); // delete lógico
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
