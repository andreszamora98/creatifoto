<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'quotes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->enum('discount_source', ['none', 'auto', 'manual'])
                    ->default('none')
                    ->after('discount_reason');
                $table->boolean('discount_applies_to_packages')
                    ->default(false)
                    ->after('discount_source');
                $table->string('auto_discount_reason', 255)
                    ->nullable()
                    ->after('discount_applies_to_packages');
            });
        }
    }

    public function down(): void
    {
        foreach (['orders', 'quotes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn([
                    'discount_source',
                    'discount_applies_to_packages',
                    'auto_discount_reason',
                ]);
            });
        }
    }
};