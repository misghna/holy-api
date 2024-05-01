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
            ['tenant_id' => 123, 'tenant_name' => 'English', 'updated_by' => 'S'],
            ['tenant_id' => 123, 'tenant_name' => 'Tigrigna', 'updated_by' => 'S'],
            ['tenant_id' => 123, 'tenant_name' => 'Arabic', 'updated_by' => 'S'],
            ['tenant_id' => 123, 'tenant_name' => 'Spanish', 'updated_by' => 'S'],
            //Add more tenants as needed
        ];
        Tenant::insert($tenants);
    }
}
