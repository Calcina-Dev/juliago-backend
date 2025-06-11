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
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('numero'); // puede ser texto o número
            $table->enum('estado', ['libre', 'ocupada', 'cerrada'])->default('libre');
            $table->string('descripcion')->nullable();
            $table->softDeletes(); // eliminación lógica
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
