<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('discount_type', ['fixed', 'percent'])->nullable()->after('discount_amount');
            $table->decimal('discount_value', 10, 2)->nullable()->after('discount_type');
            $table->enum('discount_source', ['none', 'auto', 'manual'])->default('none')->after('discount_value');
            $table->string('discount_reason', 255)->nullable()->after('discount_source');
            $table->string('auto_discount_reason', 255)->nullable()->after('discount_reason');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'discount_type',
                'discount_value',
                'discount_source',
                'discount_reason',
                'auto_discount_reason',
            ]);
        });
    }
};