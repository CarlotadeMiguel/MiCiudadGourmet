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
        // Tabla de categorías para clasificar restaurantes
        // name: string, obligatorio
        // timestamps

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nombre de la categoría
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
