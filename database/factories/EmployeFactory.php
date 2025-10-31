<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\Poste;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->unique()->phoneNumber(),
            'date_naissance' => $this->faker->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),

            'service_id' => Service::inRandomOrder()->first()->id ?? Service::factory(),
            'poste_id' => Poste::inRandomOrder()->first()->id ?? Poste::factory(),
        ];
    }
}
