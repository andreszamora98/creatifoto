<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->text('package_notes')->nullable()->after('men_features');
        });

        Schema::table('order_packages', function (Blueprint $table) {
            $table->text('package_notes_snapshot')->nullable()->after('discount_amount_snapshot');
        });

        Schema::table('quote_packages', function (Blueprint $table) {
            $table->text('package_notes_snapshot')->nullable()->after('discount_amount_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('package_notes');
        });

        Schema::table('order_packages', function (Blueprint $table) {
            $table->dropColumn('package_notes_snapshot');
        });

        Schema::table('quote_packages', function (Blueprint $table) {
            $table->dropColumn('package_notes_snapshot');
        });
    }
};