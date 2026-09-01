<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // total = SUM(order_photo_type) + SUM(order_package) + servicios adicionales a nivel orden
            $table->decimal('total', 10, 2)->default(0);

            // toma elegida de las 3 fotos (1, 2 o 3)
            $table->unsignedTinyInteger('pose_number')->nullable();

            $table->text('notes')->nullable();
            $table->date('estimated_delivery_date');
            $table->date('actual_delivery_date')->nullable();
            $table->enum('status', ['pending', 'processing', 'ready', 'delivered'])
                  ->default('pending');

            $table->foreignId('signature_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('estimated_delivery_date');
            $table->index('actual_delivery_date');
            $table->index('status');
            $table->index('signature_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};