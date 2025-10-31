<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
            PosteSeeder::class,
            EmployeSeeder::class,
            BadgeSeeder::class,
            PortiqueSeeder::class,
            PointageSeeder::class,
        ]);
    }
}
