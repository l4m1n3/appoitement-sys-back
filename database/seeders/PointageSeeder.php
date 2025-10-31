<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pointage;

class PointageSeeder extends Seeder
{
    public function run(): void
    {
        Pointage::factory()->count(50)->create();
    }
}
