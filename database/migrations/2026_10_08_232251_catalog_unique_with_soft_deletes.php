<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', fn (Blueprint $t) => $t->dropUnique('schools_name_unique'));
        Schema::table('schools', function (Blueprint $t) {
            $t->string('name_active')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN name ELSE NULL END');
            $t->unique('name_active', 'schools_name_active_unique');
        });

        Schema::table('photo_types', fn (Blueprint $t) => $t->dropUnique('photo_types_slug_unique'));
        Schema::table('photo_types', function (Blueprint $t) {
            $t->string('slug_active')->nullable()
                ->storedAs('CASE WHEN deleted_at IS NULL THEN slug ELSE NULL END');
            $t->unique('slug_active', 'photo_types_slug_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('photo_types', function (Blueprint $t) {
            $t->dropUnique('photo_types_slug_active_unique');
            $t->dropColumn('slug_active');
        });
        Schema::table('photo_types', fn (Blueprint $t) => $t->unique('slug'));

        Schema::table('schools', function (Blueprint $t) {
            $t->dropUnique('schools_name_active_unique');
            $t->dropColumn('name_active');
        });
        Schema::table('schools', fn (Blueprint $t) => $t->unique('name'));
    }
};