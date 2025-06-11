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
    Schema::table('users', function (Blueprint $table) {
        $table->string('rol')->default('mesero'); // valores posibles: admin, mesero, cocinero, cajero
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('rol');
    });
}


   
};
