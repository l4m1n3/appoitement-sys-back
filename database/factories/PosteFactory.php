<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PosteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->randomElement([
                'Développeur', 'Comptable', 'Manager', 'Technicien', 'Secrétaire'
            ]),
        ];
    }
}
