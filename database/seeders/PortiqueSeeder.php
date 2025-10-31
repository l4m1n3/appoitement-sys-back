<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Portique;

class PortiqueSeeder extends Seeder
{
    public function run(): void
    {
        Portique::factory()->count(3)->create();
    }
}
