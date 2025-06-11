<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas');
            $table->enum('estado', ['pendiente', 'en_proceso', 'servido', 'pagado', 'cerrado', 'cancelado'])->default('pendiente');
            $table->decimal('total', 8, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
