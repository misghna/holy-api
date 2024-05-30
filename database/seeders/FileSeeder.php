<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dummy data for files
        $files = [
            ['group_id' => 1, 'file_id' => '12345', 'file_name' => 'file1.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 1, 'file_id' => '23456', 'file_name' => 'file2.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 2, 'file_id' => '34567', 'file_name' => 'file3.jpg', 'created_at' => now(), 'updated_at' => now()],
            ['group_id' => 2, 'file_id' => '45678', 'file_name' => 'file4.jpg', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insert the dummy data into the files table
        DB::table('files')->insert($files);
    }
}
