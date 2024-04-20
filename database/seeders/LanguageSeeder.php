<?php

namespace Database\Seeders;
use App\Models\Language;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::truncate();

       $languages = [
            ['lang_id' => 'english', 'lang_name' => 'English'],
            ['lang_id' => 'amharic', 'lang_name' => 'ኣምሓርኛ'],
            ['lang_id' => 'tigrigna', 'lang_name' => 'ትግርኛ'],
            ['lang_id' => 'arabic', 'lang_name' => 'عربي'],
            ['lang_id' => 'spanish', 'lang_name' => 'española']
            //Add more languages as needed
        ];
        Language::insert($languages);
        
    }
}
