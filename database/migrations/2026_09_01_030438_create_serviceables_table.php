<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serviceables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('additional_service_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('serviceable_id');
            $table->string('serviceable_type');
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['serviceable_type', 'serviceable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serviceables');
    }
};