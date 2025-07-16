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
        // Tabla de restaurantes creados por usuarios
        // name: string, obligatorio
        // address: string, obligatorio
        // phone: string, opcional
        // user_id: referencia a users.id, obligatorio
        // timestamps

        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nombre del restaurante
            $table->string('address'); // dirección
            $table->string('phone')->nullable(); // teléfono, opcional
            $table->unsignedBigInteger('user_id'); // foreign: user_id -> users.id, cascade on delete
            $table->timestamps();

            // Relación foránea
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
