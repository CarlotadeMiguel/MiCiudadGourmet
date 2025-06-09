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
        // Tabla de fotos para asociar imágenes a restaurantes o reseñas (polimórfico)
        // url: string, obligatorio
        // imageable_id: id del modelo relacionado (restaurant o review)
        // imageable_type: clase del modelo relacionado
        // timestamps

        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('url'); // URL de la imagen
            $table->unsignedBigInteger('imageable_id'); // id polimórfico
            $table->string('imageable_type'); // tipo polimórfico
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
