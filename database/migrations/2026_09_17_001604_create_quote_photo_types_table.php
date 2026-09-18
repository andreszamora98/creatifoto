<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_photo_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quote_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('photo_type_id')
                  ->constrained()
                  ->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);

            $table->enum('format', ['color', 'bn'])
                  ->default('color');

            $table->foreignId('paper_type_id')
                  ->nullable()
                  ->constrained()
                  ->restrictOnDelete();

            $table->timestamps();

            $table->unique(['quote_id', 'photo_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_photo_types');
    }
};