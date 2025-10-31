<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PortiqueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->randomElement(['Entrée principale', 'Sortie principale', 'Portail arrière', 'Accès RH']),
            'emplacement' => $this->faker->randomElement(['Bâtiment A', 'Bâtiment B', 'Annexe', 'Siège central']),
            'actif' => true,
        ];
    }
}
