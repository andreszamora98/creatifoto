<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backgrounds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
            $table->softDeletes();
            $table->string('name_active', 100)->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');
            $table->unique('name_active', 'backgrounds_name_active_unique');
        });

        $now = now();
        DB::table('backgrounds')->insert([
            ['name' => 'Blanco',   'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gris',     'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('backgrounds');
    }
};