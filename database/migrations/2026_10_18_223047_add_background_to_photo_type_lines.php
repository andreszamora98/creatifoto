<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_photo_types', function (Blueprint $table) {
            $table->string('background', 255)->default('blanco')->after('format');
        });

        Schema::table('quote_photo_types', function (Blueprint $table) {
            $table->string('background', 255)->default('blanco')->after('format');
        });

        Schema::table('order_photo_types', function (Blueprint $table) {
            $table->string('background', 255)->default('blanco')->after('format');
        });
    }

    public function down(): void
    {
        Schema::table('package_photo_types', function (Blueprint $table) {
            $table->dropColumn('background');
        });

        Schema::table('quote_photo_types', function (Blueprint $table) {
            $table->dropColumn('background');
        });

        Schema::table('order_photo_types', function (Blueprint $table) {
            $table->dropColumn('background');
        });
    }
};