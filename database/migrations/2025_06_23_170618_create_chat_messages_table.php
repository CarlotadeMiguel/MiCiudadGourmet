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
        Schema::create('chat_messages', function (Blueprint $table) {
        	$table->id();
    		$table->foreignId('conversation_id')->constrained('chat_conversations')->onDelete('cascade');
    		$table->enum('role', ['user', 'assistant']);
    		$table->text('content');
    		$table->json('context_data')->nullable(); // Para almacenar datos de restaurantes
    		$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
