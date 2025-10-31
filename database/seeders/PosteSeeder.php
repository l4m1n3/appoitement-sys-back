<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Poste;

class PosteSeeder extends Seeder
{
    public function run(): void
    {
        Poste::factory()->count(5)->create();
    }
}
