<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;
use App\Models\Employe;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Employe::all()->each(function ($employe) {
            Badge::factory()->create([
                'employe_id' => $employe->id,
            ]);
        });
    }
}
