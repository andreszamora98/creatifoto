<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft-delete en catálogos
        foreach (['schools', 'photo_types', 'paper_types', 'additional_services'] as $table) {
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->softDeletes());
            }
        }

        // Snapshots de escuela en paquetes de orden/cotización
        foreach (['order_packages', 'quote_packages'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('school_id')->nullable()->after('package_id');
                $t->string('school_name_snapshot')->nullable()->after('package_name_snapshot');
            });
        }

        // Líneas de foto: nombres snapshot + vínculo opcional al paquete que las originó
        Schema::table('order_photo_types', function (Blueprint $t) {
            $t->string('photo_type_name_snapshot')->nullable()->after('photo_type_id');
            $t->string('paper_type_name_snapshot')->nullable()->after('paper_type_id');
            $t->foreignId('order_package_id')->nullable()
                ->constrained('order_packages')->cascadeOnDelete();
        });

        Schema::table('quote_photo_types', function (Blueprint $t) {
            $t->string('photo_type_name_snapshot')->nullable()->after('photo_type_id');
            $t->string('paper_type_name_snapshot')->nullable()->after('paper_type_id');
            $t->foreignId('quote_package_id')->nullable()
                ->constrained('quote_packages')->cascadeOnDelete();
        });

        // Servicios adicionales
        Schema::table('serviceables', function (Blueprint $t) {
            $t->string('additional_service_name_snapshot')->nullable();
        });

        // Total con el que nació la orden
        Schema::table('orders', function (Blueprint $t) {
            $t->decimal('initial_total', 10, 2)->nullable()->after('total');
        });
        Schema::table('quote_photo_types', fn (Blueprint $t) => $t->index('quote_id', 'qpt_quote_id_idx'));
        Schema::table('quote_photo_types', fn (Blueprint $t) => $t->dropUnique(['quote_id', 'photo_type_id']));
        Schema::table('orders', function (Blueprint $t) {
            $t->dropForeign(['customer_id']);
            $t->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $t) {
            $t->dropForeign(['customer_id']);
            $t->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $t->dropColumn('initial_total');
        });
        Schema::table('serviceables', fn (Blueprint $t) => $t->dropColumn('additional_service_name_snapshot'));
        Schema::table('quote_photo_types', function (Blueprint $t) {
            $t->dropConstrainedForeignId('quote_package_id');
            $t->dropColumn(['photo_type_name_snapshot', 'paper_type_name_snapshot']);
            $t->dropIndex('qpt_quote_id_idx');
        });
        Schema::table('order_photo_types', function (Blueprint $t) {
            $t->dropConstrainedForeignId('order_package_id');
            $t->dropColumn(['photo_type_name_snapshot', 'paper_type_name_snapshot']);
        });
        foreach (['order_packages', 'quote_packages'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['school_id', 'school_name_snapshot']));
        }
        foreach (['schools', 'photo_types', 'paper_types', 'additional_services'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
        }
    }
};