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
        
                Schema::create('menu_producto', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('menu_id')->constrained()->onDelete('cascade');
                    $table->foreignId('producto_id')->constrained()->onDelete('cascade');
                    $table->decimal('precio', 8, 2);
                    $table->timestamps();
                    
                    $table->unique(['menu_id', 'producto_id']); // para evitar duplicados
                });
            

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_producto');
    }
};
