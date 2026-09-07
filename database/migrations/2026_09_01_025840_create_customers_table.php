<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('phone', 10)->nullable()->unique();
            $table->softDeletes();
            $table->timestamps(); //(created_at, updated_at)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};