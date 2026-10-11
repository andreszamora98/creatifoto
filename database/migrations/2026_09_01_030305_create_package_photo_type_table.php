<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_photo_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_type_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->enum('format', ['color', 'bn'])->default('color');
            $table->foreignId('paper_type_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_photo_types');
    }
};