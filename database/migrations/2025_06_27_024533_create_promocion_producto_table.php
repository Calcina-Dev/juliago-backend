<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promocion_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');

            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->decimal('precio_promocional', 8, 2)->nullable(); // opcional
            $table->timestamps();

            $table->unique(['promocion_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_producto');
    }
};
