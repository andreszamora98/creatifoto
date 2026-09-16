<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //infantil, credencial, diploma, título, cartilla, filiación, visa, pasaporte, migración, postal
            $table->string('slug')->unique();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_types');
    }
};