<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('list_price', 10, 2)->nullable()->after('total_price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('list_price');
        });

        Schema::table('order_packages', function (Blueprint $table) {
            $table->decimal('list_price_snapshot', 10, 2)->nullable()->after('price_snapshot');
            $table->decimal('discount_amount_snapshot', 10, 2)->default(0)->after('list_price_snapshot');
        });

        Schema::table('quote_packages', function (Blueprint $table) {
            $table->decimal('list_price_snapshot', 10, 2)->nullable()->after('price_snapshot');
            $table->decimal('discount_amount_snapshot', 10, 2)->default(0)->after('list_price_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['list_price', 'discount_amount']);
        });

        Schema::table('order_packages', function (Blueprint $table) {
            $table->dropColumn(['list_price_snapshot', 'discount_amount_snapshot']);
        });

        Schema::table('quote_packages', function (Blueprint $table) {
            $table->dropColumn(['list_price_snapshot', 'discount_amount_snapshot']);
        });
    }
};