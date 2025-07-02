<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receta_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained()->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained()->onDelete('cascade');
            $table->decimal('cantidad', 8, 2); // puede ser gramos, unidades, ml, etc.
            $table->string('unidad', 20)->nullable(); // opcional: gr, ml, und
            $table->timestamps();

            $table->unique(['receta_id', 'insumo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_insumo');
    }
};
