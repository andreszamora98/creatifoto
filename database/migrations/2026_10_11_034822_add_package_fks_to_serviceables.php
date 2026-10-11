<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serviceables', function (Blueprint $table) {
            $table->foreignId('order_package_id')->nullable()->after('serviceable_type')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_package_id')->nullable()->after('order_package_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('serviceables', function (Blueprint $table) {
            $table->dropForeign(['order_package_id']);
            $table->dropForeign(['quote_package_id']);
            $table->dropColumn(['order_package_id', 'quote_package_id']);
        });
    }
};