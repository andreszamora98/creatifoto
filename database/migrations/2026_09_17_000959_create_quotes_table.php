<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();

            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'expired',
                'converted'
            ])->default('pending');

            $table->softDeletes();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};