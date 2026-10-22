<?php

namespace Database\Seeders;

use App\Models\Background;
use App\Models\PaperType;
use Illuminate\Database\Seeder;

class PaperTypeAndBackgroundSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Mate', 'Semimate'] as $name) {
            PaperType::firstOrCreate(['name' => $name]);
        }

        foreach (['Blanco', 'Gris'] as $name) {
            Background::firstOrCreate(['name' => $name]);
        }
    }
}