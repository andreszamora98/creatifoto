<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_photo_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_type_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2); // snapshot al momento de la orden

            // varían por cada tipo de foto dentro de la misma orden
            $table->enum('format', ['color', 'bn'])->default('color');
            $table->foreignId('paper_type_id')->nullable()->constrained()->restrictOnDelete();

            $table->timestamps();

            $table->unique(['order_id', 'photo_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_photo_type');
    }
};