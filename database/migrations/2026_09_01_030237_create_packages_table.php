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
            $table->unsignedInteger('sequence');
            $table->string('name')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->json('women_features')->nullable();
            $table->json('men_features')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unsignedInteger('sequence_active')
                ->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN sequence ELSE NULL END');

            $table->index('sequence');
            $table->unique(['school_id', 'sequence_active'], 'packages_school_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};