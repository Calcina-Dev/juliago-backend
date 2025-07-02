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
       Schema::create('subcuentas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->constrained('empresas');
    $table->foreignId('pedido_id')->constrained('pedidos');
    $table->string('nombre')->nullable();
    $table->decimal('total', 10, 2)->default(0);
    $table->timestamps();
    $table->softDeletes();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcuentas');
    }
};
