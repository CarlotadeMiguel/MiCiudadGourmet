<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración: añade la columna 'description' después de 'address'.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Añade la columna de tipo text, permite nulos y se ubica tras 'address'
            $table->text('description')->nullable()->after('address');
        });
    }

    /**
     * Revierte la migración: elimina la columna 'description'.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
