<?php

namespace Database\Seeders;
use App\Models\Tenant;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::truncate();

       $tenants = [
           [ 
        'tenant_name' => 'Enda Slasie', 
        'updated_by' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [ 
        'tenant_name' => 'Enda Gabr', 
        'updated_by' => 1,
        'created_at' => now(),
        'updated_at' => now()
    ],
           
            //Add more tenants as needed
        ];
        Tenant::insert($tenants);
    }
}
