<?php

namespace Database\Seeders;

use App\Models\MaintenanceOrder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaintenanceOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MaintenanceOrder::factory(30)->create();
    }
}
