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
        // Tabla de reseñas para que los usuarios valoren restaurantes
        // rating: entero entre 1 y 5, obligatorio
        // comment: texto, opcional
        // user_id: referencia a users.id
        // restaurant_id: referencia a restaurants.id
        // timestamps

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('rating'); // rating: 1-5
            $table->text('comment')->nullable(); // comentario opcional
            $table->unsignedBigInteger('user_id'); // foreign: user_id -> users.id, cascade on delete
            $table->unsignedBigInteger('restaurant_id'); // foreign: restaurant_id -> restaurants.id, cascade on delete
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
