<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paper_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //mate, brillante, satinado, etc.
            $table->decimal('extra_price', 10, 2)->nullable(); //costo adicional si ese papel lo amerita
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_types');
    }
};