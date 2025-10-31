<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employe;

class EmployeSeeder extends Seeder
{
    public function run(): void
    {
        Employe::factory()->count(20)->create();
    }
}
