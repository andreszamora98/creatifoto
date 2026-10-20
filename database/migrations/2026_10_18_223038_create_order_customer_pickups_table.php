<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_customer_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('name_snapshot');
            $table->string('relationship', 50)->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'customer_id'], 'ocp_order_customer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_customer_pickups');
    }
};