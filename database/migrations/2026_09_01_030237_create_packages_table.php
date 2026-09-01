<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('package_number'); // 1, 2, 3... independiente por escuela
            $table->string('name')->nullable(); // titulación, graduación, general...
            $table->decimal('total_price', 10, 2)->default(0); // cache, calculado desde package_photo_type
            $table->json('women_features')->nullable();
            $table->json('men_features')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['school_id', 'package_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};