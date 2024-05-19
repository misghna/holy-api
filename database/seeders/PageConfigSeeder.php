<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader; // Make sure to install the league/csv package

class PageConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Specify the correct path and delimiter for the CSV file
        $csv = Reader::createFromPath(base_path('page_config.csv'), 'r');
        $csv->setHeaderOffset(0); 
        $csv->setDelimiter(';');  
        $records = $csv->getRecords(); 

        foreach ($records as $record) {
            DB::table('page_config')->insert([
                'page_type' => $record['page_type'],
                'name' => $record['name'],
                'description' => $record['description'],
                'img_link' => $record['img_link'],
                'parent' => $record['parent'],
                'header_img' => $record['header_img'],
                'header_text' => $record['header_text'],
                'updated_by' => $record['updated_by'],
                'page_url' => $record['page_url'],
                'tenant_id' => $record['tenant_id'],
                'created_at' => $record['created_at'],
                'updated_at' => $record['updated_at'],
                'seq_no' => $record['seq_no'],
                'language' => $record['language']
            ]);
        }
    }
}
