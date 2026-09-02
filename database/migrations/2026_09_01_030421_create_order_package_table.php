<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->string('package_name_snapshot')->nullable(); // por si el paquete cambia después
            $table->decimal('price_snapshot', 10, 2);
            $table->timestamps();

            $table->unique(['order_id', 'package_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_package');
    }
};