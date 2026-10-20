<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('additional_services', function (Blueprint $table) {
            $table->string('name_active')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');

            $table->unique('name_active', 'additional_services_name_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('additional_services', function (Blueprint $table) {
            $table->dropUnique('additional_services_name_active_unique');
            $table->dropColumn('name_active');
        });
    }
};