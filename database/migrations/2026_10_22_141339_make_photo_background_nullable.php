<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE order_photo_types MODIFY background VARCHAR(255) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE quote_photo_types MODIFY background VARCHAR(255) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE package_photo_types MODIFY background VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE order_photo_types SET background = 'blanco' WHERE background IS NULL");
        DB::statement("UPDATE quote_photo_types SET background = 'blanco' WHERE background IS NULL");
        DB::statement("UPDATE package_photo_types SET background = 'blanco' WHERE background IS NULL");

        DB::statement("ALTER TABLE order_photo_types MODIFY background VARCHAR(255) NOT NULL DEFAULT 'blanco'");
        DB::statement("ALTER TABLE quote_photo_types MODIFY background VARCHAR(255) NOT NULL DEFAULT 'blanco'");
        DB::statement("ALTER TABLE package_photo_types MODIFY background VARCHAR(255) NOT NULL DEFAULT 'blanco'");
    }
};