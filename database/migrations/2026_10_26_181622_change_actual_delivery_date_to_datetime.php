<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE orders MODIFY actual_delivery_date DATETIME NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE orders MODIFY actual_delivery_date DATE NULL DEFAULT NULL');
    }
};