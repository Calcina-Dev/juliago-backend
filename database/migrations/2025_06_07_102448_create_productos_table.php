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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio', 8, 2);
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('categoria_id')->nullable(); // si vas a usar categorías
            $table->softDeletes(); // <-- esto es clave para eliminación lógica
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
