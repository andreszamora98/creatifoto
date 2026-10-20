<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_name_unique');
            $table->dropUnique('customers_email_unique');
            $table->dropUnique('customers_phone_unique');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('name_active')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');
            $table->string('email_active')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN email ELSE NULL END');
            $table->string('phone_active', 10)->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN phone ELSE NULL END');

            $table->unique('name_active', 'customers_name_active_unique');
            $table->unique('email_active', 'customers_email_active_unique');
            $table->unique('phone_active', 'customers_phone_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_name_active_unique');
            $table->dropUnique('customers_email_active_unique');
            $table->dropUnique('customers_phone_active_unique');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['name_active', 'email_active', 'phone_active']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('name', 'customers_name_unique');
            $table->unique('email', 'customers_email_unique');
            $table->unique('phone', 'customers_phone_unique');
        });
    }
};