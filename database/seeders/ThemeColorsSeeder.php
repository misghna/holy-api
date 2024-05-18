<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ThemeColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themeColors = [
            ['label' => 'Blue', 'hexCode' => '#0000FF'],
            ['label' => 'Dark Gray', 'hexCode' => '#A9A9A9'],
            ['label' => 'Green', 'hexCode' => '#008000'],
        ];

        // Insert the colors into the theme_colors table
        DB::table('theme_colors')->insert($themeColors);
    }
}
