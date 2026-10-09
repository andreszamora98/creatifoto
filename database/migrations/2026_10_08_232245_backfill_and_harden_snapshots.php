<?php

use App\Models\Order;
use App\Models\OrderPhotoType;
use App\Models\Quote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Escuela de cada paquete vendido/cotizado
        foreach (['order_packages', 'quote_packages'] as $t) {
            DB::statement("
                UPDATE {$t} x
                JOIN packages p ON p.id = x.package_id
                JOIN schools s ON s.id = p.school_id
                SET x.school_id = s.id, x.school_name_snapshot = s.name
                WHERE x.school_id IS NULL
            ");
        }

        // Nombres de tipo de foto y papel
        foreach (['order_photo_types', 'quote_photo_types'] as $t) {
            DB::statement("
                UPDATE {$t} l
                JOIN photo_types pt ON pt.id = l.photo_type_id
                SET l.photo_type_name_snapshot = pt.name
                WHERE l.photo_type_name_snapshot IS NULL
            ");
            DB::statement("
                UPDATE {$t} l
                JOIN paper_types pp ON pp.id = l.paper_type_id
                SET l.paper_type_name_snapshot = pp.name
                WHERE l.paper_type_id IS NOT NULL AND l.paper_type_name_snapshot IS NULL
            ");
        }

        // Servicios
        DB::statement("
            UPDATE serviceables sv
            JOIN additional_services a ON a.id = sv.additional_service_id
            SET sv.additional_service_name_snapshot = a.name
            WHERE sv.additional_service_name_snapshot IS NULL
        ");

        DB::table('orders')->whereNull('initial_total')->update(['initial_total' => DB::raw('total')]);

        // morphMap: alias estables en lugar de nombres de clase
        DB::table('serviceables')->where('serviceable_type', Order::class)->update(['serviceable_type' => 'order']);
        DB::table('serviceables')->where('serviceable_type', Quote::class)->update(['serviceable_type' => 'quote']);
        DB::table('serviceables')->where('serviceable_type', OrderPhotoType::class)->update(['serviceable_type' => 'order_photo_type']);

        // Validación antes de endurecer
        $nulls = DB::table('order_packages')->whereNull('school_id')->orWhereNull('school_name_snapshot')->count()
            + DB::table('quote_packages')->whereNull('school_id')->orWhereNull('school_name_snapshot')->count()
            + DB::table('order_photo_types')->whereNull('photo_type_name_snapshot')->count()
            + DB::table('quote_photo_types')->whereNull('photo_type_name_snapshot')->count()
            + DB::table('serviceables')->whereNull('additional_service_name_snapshot')->count()
            + DB::table('orders')->whereNull('initial_total')->count();

        if ($nulls > 0) {
            throw new RuntimeException("Backfill incompleto: {$nulls} registros con snapshot NULL. Revisa los datos antes de continuar.");
        }

        foreach (['order_packages', 'quote_packages'] as $t) {
            Schema::table($t, function (Blueprint $b) {
                $b->unsignedBigInteger('school_id')->nullable(false)->change();
                $b->string('school_name_snapshot')->nullable(false)->change();
            });
            Schema::table($t, function (Blueprint $b) {
                $b->foreign('school_id')->references('id')->on('schools')->restrictOnDelete();
            });
        }
        foreach (['order_photo_types', 'quote_photo_types'] as $t) {
            Schema::table($t, fn (Blueprint $b) => $b->string('photo_type_name_snapshot')->nullable(false)->change());
        }
        Schema::table('serviceables', fn (Blueprint $b) => $b->string('additional_service_name_snapshot')->nullable(false)->change());
        Schema::table('orders', fn (Blueprint $b) => $b->decimal('initial_total', 10, 2)->nullable(false)->change());
    }

    public function down(): void
    {
        // Irreversible a propósito: los datos se conservan
    }
};