<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantEmbeddingsTable extends Migration
{
    public function up()
    {
        Schema::create('restaurant_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
            $table->json('embedding'); // Vector de embeddings
            $table->text('text_content'); // Texto original usado para generar el embedding
            $table->timestamps();
            
            $table->index('restaurant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('restaurant_embeddings');
    }
}
