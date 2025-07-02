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
  Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('empresa_id')->nullable()->constrained('empresas'); // null = global
    $table->string('rol');
    $table->string('label');
    $table->string('icon');
    $table->string('route');
    $table->integer('orden')->default(0);
    $table->boolean('visible')->default(true);
    $table->timestamps();
    $table->softDeletes(); // ✅
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
