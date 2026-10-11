<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE packages
            SET name = CONCAT('Paquete ', sequence)
            WHERE name IS NULL OR TRIM(name) = ''
        ");
        $duplicates = DB::table('packages')
            ->select('school_id', 'name', DB::raw('COUNT(*) as total'))
            ->whereNull('deleted_at')
            ->groupBy('school_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $detalle = $duplicates
                ->map(fn ($d) => "escuela #{$d->school_id} => \"{$d->name}\" ({$d->total} registros)")
                ->implode('; ');

            throw new RuntimeException(
                "Hay paquetes activos con nombres duplicados dentro de la misma escuela: {$detalle}. "
                . "Renómbralos antes de volver a correr la migración."
            );
        }
        Schema::table('packages', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->string('name_active')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');
            $table->unique(['school_id', 'name_active'], 'packages_school_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropUnique('packages_school_name_unique');
            $table->dropColumn('name_active');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });

    }
};