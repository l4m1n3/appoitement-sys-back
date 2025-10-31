<?php

namespace Database\Factories;

use App\Models\Employe;
use Illuminate\Database\Eloquent\Factories\Factory;

class BadgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employe_id' => Employe::inRandomOrder()->first()->id ?? Employe::factory(),
            'code_unique' => strtoupper($this->faker->unique()->bothify('RFID-#####')),
            'type' => $this->faker->randomElement(['RFID', 'QR']),
            'actif' => true,
        ];
    }
}
