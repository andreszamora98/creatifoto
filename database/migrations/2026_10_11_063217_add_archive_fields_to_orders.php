<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('archive_name', 255)->nullable()->after('pose_number');
            $table->string('pose_range', 255)->nullable()->after('archive_name');
        });

        $duplicates = DB::table('orders')
            ->select('pose_number')
            ->whereNull('deleted_at')
            ->whereNotNull('pose_number')
            ->groupBy('pose_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('pose_number');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Hay números de toma duplicados entre órdenes activas: '
                . $duplicates->implode(', ')
                . '. Resuélvelos antes de continuar.'
            );
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('pose_number_active')
                ->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN pose_number ELSE NULL END');
            $table->unique('pose_number_active', 'orders_pose_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_pose_number_unique');
            $table->dropColumn('pose_number_active');
            $table->dropColumn(['archive_name', 'pose_range']);
        });
    }
};